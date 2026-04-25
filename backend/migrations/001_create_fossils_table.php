<?php

declare(strict_types=1);

namespace PaleoCRM;

/**
 * Database Migration: Create fossils table
 * 
 * Run this via: php -r "require 'migrations/001_create_fossils_table.php';"
 * Or execute the SQL directly in your database client
 */

return [
    'up' => <<<SQL
CREATE TABLE IF NOT EXISTS fossils (
    id VARCHAR(50) PRIMARY KEY,
    species VARCHAR(255) NOT NULL,
    collection_name VARCHAR(255) NOT NULL,
    estimated_age INTEGER NOT NULL CHECK (estimated_age >= 0),
    weight FLOAT NOT NULL CHECK (weight > 0),
    status VARCHAR(50) DEFAULT 'documented' CHECK (status IN ('active', 'extinct', 'documented', 'pending-analysis')),
    discovery_location VARCHAR(255) DEFAULT 'Unknown',
    ai_description TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(50),
    changes JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);
SQL,
    'down' => <<<SQL
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS fossils;
SQL,
];
