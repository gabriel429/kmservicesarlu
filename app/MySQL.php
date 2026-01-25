<?php
// MySQLi core utilitaire (pur PHP + MySQL)

class MySQLCore {
    // Adaptation: utiliser PDO via App\Database et supporter MySQL et Postgres.
    private static $pdo = null;

    private static function connect() {
        if (self::$pdo === null) {
            require_once __DIR__ . '/Database.php';
            require_once __DIR__ . '/../config/config.php';
            self::$pdo = \App\Database::getInstance();
        }
        return self::$pdo;
    }

    public static function query($sql, $params = []) {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch($sql, $params = []) {
        $stmt = self::query($sql, $params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll() ?: [];
    }

    public static function execute($sql, $params = []) {
        $stmt = self::query($sql, $params);
        // rowCount fonctionne pour INSERT/UPDATE/DELETE sur MySQL et Postgres
        return $stmt->rowCount() >= 0;
    }

    public static function lastInsertId() {
        return self::connect()->lastInsertId();
    }
}
