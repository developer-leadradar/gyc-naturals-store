<?php
/**
 * NeonStatement — returned by Database::query() on the pgsql/HTTP path.
 * Implements the PDOStatement subset used across the codebase.
 */
class NeonStatement {
    private array  $rows;
    private int    $pos  = 0;
    private int    $cnt;
    public  string $queryString = '';

    public function __construct(array $result) {
        $this->rows = $result['rows']     ?? [];
        $this->cnt  = $result['rowCount'] ?? count($this->rows);
    }

    public function fetch(int $mode = PDO::FETCH_ASSOC): mixed {
        $row = $this->rows[$this->pos] ?? false;
        if ($row !== false) $this->pos++;
        return $row;
    }

    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array {
        return $this->rows;
    }

    public function fetchColumn(int $col = 0): mixed {
        $row = $this->rows[$this->pos] ?? false;
        if ($row === false) return false;
        $this->pos++;
        return array_values($row)[$col] ?? false;
    }

    public function rowCount(): int { return $this->cnt; }
    public function execute(array $params = []): bool { return true; }
}

/**
 * NeonPreparedStatement — returned by NeonConnection::prepare().
 * Stores the SQL, executes via the Database HTTP layer on execute().
 */
class NeonPreparedStatement {
    private Database       $db;
    private string         $sql;
    private ?NeonStatement $result = null;

    public function __construct(Database $db, string $sql) {
        $this->db  = $db;
        $this->sql = $sql;
    }

    public function execute(?array $params = null): bool {
        $this->result = $this->db->query($this->sql, $params ?? []);
        return $this->result !== false;
    }

    public function fetch(int $mode = PDO::FETCH_ASSOC): mixed {
        return $this->result ? $this->result->fetch($mode) : false;
    }

    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array {
        return $this->result ? $this->result->fetchAll($mode) : [];
    }

    public function rowCount(): int {
        return $this->result ? $this->result->rowCount() : 0;
    }
}

/**
 * NeonConnection — PDO-like facade for seed.php and helpers that call
 * getConnection()->prepare() directly.
 */
class NeonConnection {
    private Database $db;
    public function __construct(Database $db) { $this->db = $db; }

    public function prepare(string $sql): NeonPreparedStatement {
        return new NeonPreparedStatement($this->db, $sql);
    }

    public function query(string $sql): NeonStatement|false {
        return $this->db->query($sql, []);
    }

    public function exec(string $sql): int {
        $s = $this->db->query($sql, []);
        return $s ? $s->rowCount() : 0;
    }

    public function lastInsertId(?string $name = null): string { return '0'; }

    public function getAttribute(int $attr): mixed {
        return $attr === PDO::ATTR_DRIVER_NAME ? 'pgsql' : null;
    }

    public function beginTransaction(): bool { return true; }
    public function commit():           bool { return true; }
    public function rollBack():         bool { return true; }
}

/**
 * Database — singleton that supports:
 *   • MySQL    → standard PDO connection
 *   • pgsql    → Neon HTTP query API via curl on port 443
 *                (bypasses broken getaddrinfo + blocked port 5432 in Vercel)
 */
class Database {
    private static ?Database $instance = null;
    private string  $driver;

    // ── MySQL path ──────────────────────────────────────────────────────────
    private ?PDO $connection = null;

    // ── Neon HTTP path ──────────────────────────────────────────────────────
    private ?string $neonHost    = null;  // original hostname (used for SNI)
    private ?string $neonIp      = null;  // resolved A-record IP (bypass DNS)
    private ?string $neonConnStr = null;  // postgresql://user:pass@host/db

    // ───────────────────────────────────────────────────────────────────────
    // Resolve a Neon hostname to an IPv4 using dns_get_record().
    // This works in vercel-php even when gethostbyname() / getaddrinfo fail.
    // ───────────────────────────────────────────────────────────────────────
    private static function resolveHostIp(string $hostname): ?string {
        if (!function_exists('dns_get_record')) return null;
        $recs = @dns_get_record($hostname, DNS_A);
        if (is_array($recs) && !empty($recs)) {
            shuffle($recs);          // spread across Neon's multiple IPs
            return $recs[0]['ip'] ?? null;
        }
        return null;
    }

    // ───────────────────────────────────────────────────────────────────────
    // Core HTTP query helper — sends SQL to Neon's /query HTTP endpoint.
    //
    // Named params  (:foo)  are converted to PostgreSQL positional ($1, $2…)
    // before sending.  :: type-casts are left untouched (lookbehind on :).
    // ───────────────────────────────────────────────────────────────────────
    private function httpQuery(string $sql, array $params): ?array {
        if (!function_exists('curl_init')) return null;

        // Step 1: convert named  :param  →  $N  (skip  ::type-casts)
        $positional = [];
        $n          = 0;
        $converted  = preg_replace_callback(
            '/(?<![:\$])(:([a-zA-Z_][a-zA-Z0-9_]*))/',
            function ($m) use ($params, &$positional, &$n) {
                $key          = $m[2];
                $positional[] = $params[$key] ?? $params[':' . $key] ?? null;
                return '$' . (++$n);
            },
            $sql
        );
        // Step 2: if no named params were found, convert  ?  →  $N  (PDO positional style)
        if ($n === 0 && !empty($params)) {
            $positional = array_values($params);
            $converted  = preg_replace_callback('/\?/', function ($m) use (&$n) {
                return '$' . (++$n);
            }, $sql);
        }

        $payload = json_encode([
            'query'  => $converted,
            'params' => $positional,
        ]);

        $host = $this->neonHost;
        $ch   = curl_init("https://$host/query");
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "Neon-Connection-String: {$this->neonConnStr}",
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        // CURLOPT_RESOLVE injects the pre-resolved IP into curl's DNS cache,
        // so curl skips its own DNS lookup but still sends the correct hostname
        // via TLS SNI — which Neon requires for routing to the right compute.
        if ($this->neonIp) {
            $opts[CURLOPT_RESOLVE] = ["$host:443:{$this->neonIp}"];
        }
        curl_setopt_array($ch, $opts);

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err || $code !== 200) {
            error_log("Neon HTTP query failed: HTTP $code | $err | "
                    . substr($sql, 0, 120));
            return null;
        }

        $data = json_decode($resp, true);
        return is_array($data) ? $data : null;
    }

    // ───────────────────────────────────────────────────────────────────────
    private function __construct() {
        $this->driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
        $port         = defined('DB_PORT')   ? DB_PORT   : ($this->driver === 'pgsql' ? '5432' : '3306');

        if ($this->driver === 'pgsql') {
            // ── Neon HTTP API path ─────────────────────────────────────────
            // The HTTP query API works on the DIRECT endpoint (strip -pooler).
            // We intentionally do NOT use CURLOPT_RESOLVE — dns_get_record()
            // resolves the direct hostname to pooler IPs (same anycast range),
            // pinning those IPs causes the proxy to reject /query requests.
            // Letting libcurl (c-ares) handle DNS independently reaches the
            // correct compute-side proxy that accepts HTTP queries.
            //
            // We also include options=endpoint=<id> in the connection string,
            // as required by Neon c-N cluster endpoints for HTTP query routing.
            $poolerHost  = DB_HOST;
            $directHost  = preg_replace('/-pooler(?=\.)/', '', $poolerHost);
            $this->neonHost   = $directHost;
            $this->neonIp     = null;  // do NOT pin IP — let curl resolve natively

            // Extract endpoint ID from hostname (ep-xxxx-xxxx)
            $endpointId = '';
            if (preg_match('/^(ep-[a-z0-9]+-[a-z0-9]+)/', $directHost, $m)) {
                $endpointId = $m[1];
            }

            $this->neonConnStr = 'postgresql://'
                               . urlencode(DB_USER) . ':' . urlencode(DB_PASS)
                               . '@' . $directHost . '/' . DB_NAME
                               . ($endpointId ? '?options=endpoint%3D' . $endpointId : '');

            // Verify connectivity with a lightweight probe
            $check = $this->httpQuery('SELECT 1 AS ok', []);
            if ($check === null) {
                // Surface a clear error (same format callers expect)
                die('Database Connection Failed: Neon HTTP API unreachable. '
                  . 'Verify DB_HOST / DB_USER / DB_PASS environment variables '
                  . 'and that outbound HTTPS (port 443) is allowed.');
            }
        } else {
            // ── MySQL PDO path ─────────────────────────────────────────────
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . $port
                 . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('Database Connection Failed: ' . $e->getMessage());
            }
        }
    }

    // ───────────────────────────────────────────────────────────────────────
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Raw connection — PDO for MySQL, NeonConnection facade for pgsql. */
    public function getConnection(): PDO|NeonConnection {
        return $this->driver === 'pgsql'
            ? new NeonConnection($this)
            : $this->connection;
    }

    public function getDriver(): string { return $this->driver; }

    // ───────────────────────────────────────────────────────────────────────
    // Public query helpers
    // ───────────────────────────────────────────────────────────────────────
    public function query(string $sql, array $params = []): NeonStatement|false {
        if ($this->driver !== 'pgsql') {
            // MySQL: standard PDO
            try {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt;           // returns a real PDOStatement
            } catch (PDOException $e) {
                error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
                return false;
            }
        }
        // pgsql: Neon HTTP
        $result = $this->httpQuery($sql, $params);
        if ($result === null) {
            error_log('Neon query returned null | SQL: ' . $sql);
            return false;
        }
        return new NeonStatement($result);
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return [];
        return ($stmt instanceof NeonStatement)
            ? $stmt->fetchAll()
            : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne(string $sql, array $params = []): array|false|null {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return null;
        $row = ($stmt instanceof NeonStatement)
            ? $stmt->fetch()
            : $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function insert(string $table, array $data): int|string|false {
        $keys   = array_keys($data);
        $fields = implode(', ', $keys);
        $phs    = ':' . implode(', :', $keys);

        if ($this->driver === 'pgsql') {
            $sql    = "INSERT INTO $table ($fields) VALUES ($phs) RETURNING id";
            $result = $this->httpQuery($sql, $data);
            if ($result === null) return false;
            return $result['rows'][0]['id'] ?? false;
        }

        $sql  = "INSERT INTO `$table` ($fields) VALUES ($phs)";
        $stmt = $this->query($sql, $data);
        return $stmt ? $this->connection->lastInsertId() : false;
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): NeonStatement|false {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = $this->driver === 'pgsql' ? "$key = :$key" : "`$key` = :$key";
        }
        $setStr = implode(', ', $set);

        // Convert positional ? markers in $where to named :where_pN params
        $namedWhere = [];
        foreach ($whereParams as $i => $val) {
            $pName              = 'where_p' . $i;
            $where              = preg_replace('/\?/', ':' . $pName, $where, 1);
            $namedWhere[$pName] = $val;
        }

        $tbl = $this->driver === 'pgsql' ? $table : "`$table`";
        $sql = "UPDATE $tbl SET $setStr WHERE $where";
        return $this->query($sql, array_merge($data, $namedWhere));
    }

    public function delete(string $table, string $where, array $params = []): NeonStatement|false {
        $tbl = $this->driver === 'pgsql' ? $table : "`$table`";
        return $this->query("DELETE FROM $tbl WHERE $where", $params);
    }

    public function lastInsertId(?string $name = null): string {
        if ($this->driver === 'pgsql') return '0';
        return $this->connection->lastInsertId($name);
    }
}
