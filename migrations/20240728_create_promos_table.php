<?php
// migrations/20240728_create_promos_table.php

require_once __DIR__ . '/../db_config.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `promos` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `carrier` varchar(255) NOT NULL,
      `promo_name` varchar(255) NOT NULL,
      `config_text` text NOT NULL,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `icon_promo_path` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $pdo->exec($sql);

    echo "Migration successful: promos table created.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
