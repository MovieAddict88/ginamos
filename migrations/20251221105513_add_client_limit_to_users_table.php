<?php
require_once __DIR__ . '/../db_config.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN client_limit INT NOT NULL DEFAULT 0 AFTER account_id");
    echo "Column 'client_limit' added to users table successfully." . PHP_EOL;
} catch (PDOException $e) {
    die("ERROR: Could not execute. " . $e->getMessage());
}
