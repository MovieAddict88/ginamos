<?php
require_once '../db_config.php';

try {
    $sql = "ALTER TABLE users ADD COLUMN promo_id INT DEFAULT NULL";
    $pdo->exec($sql);
    echo "Migration successful: 'promo_id' column added to 'users' table.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
