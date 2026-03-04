<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        try {
            if (Config::get('db.driver') === 'sqlite') {
                $path = __DIR__ . '/../../' . Config::get('db.database');
                self::$pdo = new PDO('sqlite:' . $path);
            } else {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', Config::get('db.host'), Config::get('db.port'), Config::get('db.name'));
                self::$pdo = new PDO($dsn, Config::get('db.user'), Config::get('db.pass'));
            }
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error('DB connection failed: ' . $e->getMessage());
            die('Database connection error');
        }

        return self::$pdo;
    }
}
