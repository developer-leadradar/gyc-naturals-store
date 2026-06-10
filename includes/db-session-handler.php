<?php
/**
 * DbSessionHandler — stores PHP sessions in Neon PostgreSQL via the db-proxy.
 *
 * Required on Vercel because each PHP invocation runs in a separate ephemeral
 * container with its own /tmp directory, so file-based sessions are lost on the
 * very next request. This handler writes to the php_sessions table which is
 * shared across all containers.
 *
 * Self-contained by design: session_set_save_handler() must be called before
 * session_start(), which is before the Database singleton initialises, so we
 * duplicate the curl-to-proxy pattern here rather than depending on db.php.
 */
class DbSessionHandler implements SessionHandlerInterface {
    private string $proxyUrl;
    private string $secret;

    public function __construct() {
        $host           = $_SERVER['HTTP_HOST'] ?? 'gyc-naturals.vercel.app';
        $this->proxyUrl = 'https://' . $host . '/api/db-proxy';
        $this->secret   = getenv('DB_PROXY_SECRET') ?: '';
    }

    private function dbQuery(string $sql, array $params = []): ?array {
        if (!$this->secret || !function_exists('curl_init')) return null;
        $ch = curl_init($this->proxyUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['sql' => $sql, 'params' => $params]),
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-db-secret: ' . $this->secret,
            ],
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        if (!$raw) return null;
        return json_decode($raw, true) ?: null;
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool                          { return true; }

    public function read(string $id): string|false {
        $r = $this->dbQuery(
            'SELECT session_data FROM php_sessions WHERE session_id=$1 AND last_access>$2',
            [$id, time() - 86400]
        );
        return $r['rows'][0]['session_data'] ?? '';
    }

    public function write(string $id, string $data): bool {
        $r = $this->dbQuery(
            'INSERT INTO php_sessions(session_id,session_data,last_access) VALUES($1,$2,$3)
             ON CONFLICT(session_id) DO UPDATE SET session_data=$2, last_access=$3',
            [$id, $data, time()]
        );
        return $r !== null;
    }

    public function destroy(string $id): bool {
        $this->dbQuery('DELETE FROM php_sessions WHERE session_id=$1', [$id]);
        return true;
    }

    public function gc(int $max_lifetime): int|false {
        $r = $this->dbQuery(
            'DELETE FROM php_sessions WHERE last_access<$1',
            [time() - $max_lifetime]
        );
        return (int)($r['rowCount'] ?? 0);
    }
}
