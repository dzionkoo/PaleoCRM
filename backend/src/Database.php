<?php

declare(strict_types=1);

namespace PaleoCRM;

/**
 * Database connection factory
 * 
 * Loads configuration from .env and creates PDO instance
 */
final class Database
{
    private static ?\PDO $instance = null;

    public static function connect(): \PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $databaseUrl = $_ENV['DATABASE_URL'] ?? 'sqlite:fossil.db';

        // Parse database URL
        if (str_starts_with($databaseUrl, 'sqlite:')) {
            $path = substr($databaseUrl, 7);
            $dsn = "sqlite:{$path}";
            self::$instance = new \PDO($dsn);
        } elseif (str_starts_with($databaseUrl, 'pgsql://')) {
            // PostgreSQL connection
            $parts = parse_url($databaseUrl);
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $parts['host'] ?? 'localhost',
                $parts['port'] ?? 5432,
                substr($parts['path'] ?? '', 1)
            );
            self::$instance = new \PDO(
                $dsn,
                $parts['user'] ?? 'postgres',
                $parts['pass'] ?? ''
            );
        } else {
            throw new \RuntimeException("Unsupported database URL: {$databaseUrl}");
        }

        self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        self::$instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return self::$instance;
    }

    public static function disconnect(): void
    {
        self::$instance = null;
    }
}
