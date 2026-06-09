<?php
class Database {
    private static $instance = null;
    private $connection;
    private string $driver;

    private function __construct() {
        try {
            $this->driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
            $port         = defined('DB_PORT') ? DB_PORT : ($this->driver === 'pgsql' ? '5432' : '3306');

            if ($this->driver === 'pgsql') {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";sslmode=require";
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
