<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Manages the PDO connection and bootstraps the demo database.
 */
class Database
{
    private static ?PDO $connection = null;

    /**
     * Returns a shared PDO connection.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../config/database.php';

        try {
            self::ensureDatabaseExists($config);
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['port'], $config['database'], $config['charset']);
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::migrate(self::$connection);

            return self::$connection;
        } catch (PDOException $exception) {
            self::logError($exception);
            throw $exception;
        }
    }

    /**
     * Creates the configured database when missing.
     *
     * @param array<string, string> $config
     */
    private static function ensureDatabaseExists(array $config): void
    {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $config['host'], $config['port'], $config['charset']);
        $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $database = str_replace('`', '``', $config['database']);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Runs idempotent schema creation and seed statements.
     */
    private static function migrate(PDO $pdo): void
    {
        $schema = file_get_contents(__DIR__ . '/../schema.sql');
        if ($schema === false) {
            return;
        }

        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }
    }

    /**
     * Writes database errors into logs/error.log.
     */
    private static function logError(PDOException $exception): void
    {
        $message = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $exception->getMessage());
        file_put_contents(__DIR__ . '/../logs/error.log', $message, FILE_APPEND);
    }
}
