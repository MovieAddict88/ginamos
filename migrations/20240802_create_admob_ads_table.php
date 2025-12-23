<?php
require_once __DIR__ . '/../db_config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admob_ads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            ad_unit_id VARCHAR(255) NOT NULL,
            ad_type VARCHAR(255) NOT NULL DEFAULT 'banner',
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    // Migration successful, do nothing.
} catch (PDOException $e) {
    error_log("Migration failed: " . $e->getMessage());
}
