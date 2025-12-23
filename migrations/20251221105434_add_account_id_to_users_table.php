<?php
require_once __DIR__ . '/../db_config.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN account_id INT NULL AFTER is_reseller");
    echo "Column 'account_id' added to users table successfully." . PHP_EOL;
} catch (PDOException $e) {
    die("ERROR: Could not execute. " . $e->getMessage());
}
