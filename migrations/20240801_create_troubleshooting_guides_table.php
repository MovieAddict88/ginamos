<?php
require_once __DIR__ . '/../db_config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `troubleshooting_guides` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'troubleshooting_guides' created successfully.";
} catch (PDOException $e) {
    die("Could not create table: " . $e->getMessage());
}
?>
