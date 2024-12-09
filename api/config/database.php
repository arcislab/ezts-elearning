<?php
class Database {
    private static $connection;

    public static function connect() {
        if (!self::$connection) {
            self::$connection = new mysqli('localhost', 'root', '', 'ezts_elearn');
            if (self::$connection->connect_error) {
                die('Database connection failed: ' . self::$connection->connect_error);
            }
        }
        return self::$connection;
    }
}
?>