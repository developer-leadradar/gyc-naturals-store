<?php
class Database {
    private static $instance = null;
    private $connection;
    private string $driver;

    /**
     * Resolve a Neon PostgreSQL hostname to an IPv4 address using
     * PHP's dns_get_record(), which queries DNS directly and works
     * even when getaddrinfo / gethostbyname are broken for long hostnames
     * in serverless environments (Vercel + vercel-php).
     *
     * Returns [ip, endpointId] or [null, null] on failure.
     *   ip         — one of the resolved A-record IPs (shuffled for load distribution)
     *   endpointId — Neon endpoint ID extracted from the hostname,
     *                used for SNI-less routing via the PostgreSQL `options` parameter
     */
    private static function resolveNeonHost(string $hostname): array {
        // Extract Neon endpoint ID from hostname:
        //   ep-withered-queen-aq0iyxzp-pooler.c-8.us-east-1.aws.neon.tech
        //   → ep-withered-queen-aq0iyxzp
        $endpointId = preg_match(
            '/^(ep-[a-z0-9-]+?)(?:-pooler)?(?:\.|$)/',
            $hostname,
            $m
        ) ? $m[1] : null;

        if (!function_exists('dns_get_record')) return [null, $endpointId];
        $records = @dns_get_record($hostname, DNS_A);
        if (is_array($records) && !empty($records)) {
            shuffle($records);                    // spread load across Neon's IPs
            $ip = $records[0]['ip'] ?? null;
            return [$ip, $endpointId];
        }
        return [null, $endpointId];
    }

    private function __construct() {
        try {
            $this->driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
            $port         = defined('DB_PORT') ? DB_PORT : ($this->driver === 'pgsql' ? '5432' : '3306');

            if ($this->driver === 'pgsql') {
                $host = DB_HOST;

                // In Vercel + vercel-php, PHP's getaddrinfo fails with EAI_SYSTEM for
                // long cloud hostnames (broken NSS config), but dns_get_record() works.
                //
                // Strategy:
                //   1. dns_get_record() → get a real IPv4 (52.x.x.x etc.)
                //   2. Use the IP as host= so PHP's socket code sees a numeric address
                //      (no DNS lookup needed for numeric IPs → getaddrinfo bypassed)
                //   3. Add options=endpoint=ep-xxx as a PostgreSQL startup parameter
                //      so Neon can route to the correct compute without SNI
                //      (Neon explicitly supports this for SNI-less environments)
                [$resolvedIp, $endpointId] = self::resolveNeonHost($host);

                if ($resolvedIp) {
                    // Numeric host= bypasses getaddrinfo; endpoint option routes on Neon
                    $opts = $endpointId ? ";options=endpoint=$endpointId" : '';
                    $dsn  = "pgsql:host=$resolvedIp;port=$port;dbname=" . DB_NAME . ";sslmode=require$opts";
                } else {
                    // Fallback: use original hostname (may fail in serverless)
                    $dsn = "pgsql:host=$host;port=$port;dbname=" . DB_NAME . ";sslmode=require";
                }
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            }

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() { return $this->connection; }
    public function getDriver()     { return $this->driver; }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }

    public function insert($table, $data) {
        $keys         = array_keys($data);
        $fields       = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        // Use RETURNING id for PostgreSQL to get the inserted ID reliably
        if ($this->driver === 'pgsql') {
            $sql  = "INSERT INTO $table ($fields) VALUES ($placeholders) RETURNING id";
            $stmt = $this->query($sql, $data);
            if (!$stmt) return false;
            $row = $stmt->fetch();
            return $row ? $row['id'] : false;
        }
        $sql  = "INSERT INTO `$table` ($fields) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);
        return $stmt ? $this->connection->lastInsertId() : false;
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = $this->driver === 'pgsql' ? "$key = :$key" : "`$key` = :$key";
        }
        $setString = implode(', ', $set);

        $namedWhereParams = [];
        foreach ($whereParams as $i => $val) {
            $paramName                    = 'where_p' . $i;
            $where                        = preg_replace('/\?/', ':' . $paramName, $where, 1);
            $namedWhereParams[$paramName] = $val;
        }

        $sql = $this->driver === 'pgsql'
            ? "UPDATE $table SET $setString WHERE $where"
            : "UPDATE `$table` SET $setString WHERE $where";
        return $this->query($sql, array_merge($data, $namedWhereParams));
    }

    public function delete($table, $where, $params = []) {
        $sql = $this->driver === 'pgsql'
            ? "DELETE FROM $table WHERE $where"
            : "DELETE FROM `$table` WHERE $where";
        return $this->query($sql, $params);
    }

    public function lastInsertId($name = null) {
        return $this->connection->lastInsertId($name);
    }
}
