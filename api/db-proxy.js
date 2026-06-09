/**
 * api/db-proxy.js — Node.js → Neon PostgreSQL proxy
 *
 * vercel-php cannot open TCP sockets, so PHP sends SQL here over HTTPS
 * and this Node.js function (which CAN use TCP) forwards it to Neon via pg.
 *
 * POST /api/db-proxy
 * Header : x-db-secret: <DB_PROXY_SECRET>
 * Body   : { "sql": "SELECT …", "params": [] }
 * Returns: { "rows": […], "rowCount": N }  on success
 *          { "error": "…" }               on failure (4xx / 5xx)
 */

const { Pool } = require('pg');

let pool = null;

function getPool() {
  if (!pool) {
    const host     = (process.env.DB_HOST     || '').trim();
    const user     = (process.env.DB_USER     || '').trim();
    const password = (process.env.DB_PASS     || '').trim();
    const database = (process.env.DB_NAME     || 'neondb').trim();
    const port     = parseInt((process.env.DB_PORT || '5432').trim(), 10);

    pool = new Pool({
      host,
      user,
      password,
      database,
      port,
      ssl: { rejectUnauthorized: false },
      max: 3,
      idleTimeoutMillis:       30000,
      connectionTimeoutMillis: 10000,
    });

    pool.on('error', (err) => {
      console.error('[db-proxy] pool error — resetting:', err.message);
      pool = null; // force re-create on next request
    });
  }
  return pool;
}

module.exports = async function handler(req, res) {
  // ── Method guard ────────────────────────────────────────────────────────
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  // ── Shared-secret auth ───────────────────────────────────────────────────
  const expected = (process.env.DB_PROXY_SECRET || '').trim();
  const provided  = (req.headers['x-db-secret']  || '').trim();

  if (!expected || provided !== expected) {
    console.warn('[db-proxy] unauthorized request from', req.headers['x-forwarded-for'] || 'unknown');
    return res.status(401).json({ error: 'Unauthorized' });
  }

  // ── Parse body ───────────────────────────────────────────────────────────
  const body   = req.body || {};
  const sql    = typeof body.sql === 'string' ? body.sql.trim() : '';
  const params = Array.isArray(body.params)   ? body.params     : [];

  if (!sql) {
    return res.status(400).json({ error: 'Missing or invalid sql' });
  }

  // ── Execute ───────────────────────────────────────────────────────────────
  try {
    const result = await getPool().query(sql, params.length ? params : undefined);
    return res.status(200).json({
      rows:     result.rows,
      rowCount: result.rowCount ?? result.rows.length,
    });
  } catch (err) {
    console.error('[db-proxy] query error:', err.message, '| sql:', sql.slice(0, 200));
    return res.status(500).json({ error: err.message });
  }
};
