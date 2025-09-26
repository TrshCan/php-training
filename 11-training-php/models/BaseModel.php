<?php
// models/BaseModel.php
require_once __DIR__ . '/../configs/database.php';

abstract class BaseModel {
    /** @var mysqli|null */
    protected static $_connection = null;

    public function __construct() {
        if (self::$_connection === null) {
            // Use DB constants from configs/database.php (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT)
            $host = defined('DB_HOST') ? DB_HOST : getenv('DB_HOST');
            $user = defined('DB_USER') ? DB_USER : getenv('DB_USER');
            $pass = defined('DB_PASSWORD') ? DB_PASSWORD : getenv('DB_PASSWORD');
            $db   = defined('DB_NAME') ? DB_NAME : getenv('DB_NAME');
            $port = defined('DB_PORT') ? (int)DB_PORT : ((int)getenv('DB_PORT') ?: 3306);

            $mysqli = new mysqli($host, $user, $pass, $db, $port);

            if ($mysqli->connect_errno) {
                // In development it's helpful to see the error; in production log instead.
                error_log("MySQL connection error: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
                die("Database connection failed");
            }

            // Ensure utf8mb4 for emoji and proper encoding
            $mysqli->set_charset('utf8mb4');

            self::$_connection = $mysqli;
        }
    }

    /**
     * Return mysqli connection
     * @return mysqli
     */
    protected function getConnection() {
        return self::$_connection;
    }

    /**
     * Execute a SQL query (use prepared statements for user input)
     * @param string $sql
     * @return mysqli_result|bool
     * @throws Exception on error
     */
    protected function query(string $sql) {
        $result = self::$_connection->query($sql);
        if ($result === false) {
            // Throwing here during development is useful; in production consider logging and returning false.
            throw new Exception("MySQL query error: " . self::$_connection->error . " -- SQL: " . $sql);
        }
        return $result;
    }

    /**
     * Convenience: fetch rows for a simple SELECT (not for user-controlled SQL)
     * @param string $sql
     * @return array
     */
    protected function select(string $sql): array {
        $result = $this->query($sql);
        $rows = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        return $rows;
    }

    /**
     * Insert (fallback). Prefer prepared statements in models.
     * @param string $sql
     * @return int|bool last insert id or false
     */
    protected function insert(string $sql) {
        $res = $this->query($sql);
        return $res === true ? self::$_connection->insert_id : $res;
    }

    /**
     * Update fallback (use prepared statements instead)
     * @param string $sql
     * @return bool|mysqli_result
     */
    protected function update(string $sql) {
        return $this->query($sql);
    }

    /**
     * Delete fallback (use prepared statements instead)
     * @param string $sql
     * @return bool|mysqli_result
     */
    protected function delete(string $sql) {
        return $this->query($sql);
    }
}
