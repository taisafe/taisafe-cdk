<?php

namespace Taisafe\Repositories;

use PDO;
use RuntimeException;

/**
 * Provides shared PDO connection (T009).
 */
class DatabaseConnection
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $configPath = __DIR__ . '/../../config/env.php';
        if (! file_exists($configPath)) {
            $configPath = __DIR__ . '/../../config/env.example.php';
        }
        /** @var array{db:array} $config */
        $config = require $configPath;
        $db = $config['db'] ?? null;
        if (! $db) {
            throw new RuntimeException('Database configuration missing');
        }
        $charset = $db['charset'] ?? 'utf8mb4';
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $charset);
        self::$pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return self::$pdo;
    }
}
