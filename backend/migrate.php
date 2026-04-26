<?php

declare(strict_types=1);

/**
 * Database Migration Runner
 * Usage: php migrate.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use PaleoCRM\Database;

// Load .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, '=') && !str_starts_with(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Connect to database
$pdo = Database::connect();

// Run migration
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS fossils (
    id VARCHAR(50) PRIMARY KEY,
    species VARCHAR(255) NOT NULL,
    collection_name VARCHAR(255) NOT NULL,
    estimated_age INTEGER NOT NULL,
    weight FLOAT NOT NULL,
    status VARCHAR(50) DEFAULT 'documented',
    discovery_location VARCHAR(255) DEFAULT 'Unknown',
    ai_description TEXT,
    metadata TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(50),
    changes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_fossils_status ON fossils(status);
CREATE INDEX IF NOT EXISTS idx_fossils_created ON fossils(created_at);
CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit_logs(entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at);
SQL;

try {
    // SQLite doesn't support multiple statements in exec(), so split them
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($stmt) => !empty($stmt)
    );

    foreach ($statements as $statement) {
        $pdo->exec($statement . ';');
        echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
    }

    echo "\n✅ Database migration completed successfully!\n";
} catch (\PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
