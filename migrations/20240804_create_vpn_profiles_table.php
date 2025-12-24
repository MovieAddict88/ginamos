<?php
// migrations/20240804_create_vpn_profiles_table.php

require_once __DIR__ . '/../db_config.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `vpn_profiles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `ovpn_config` text NOT NULL,
      `type` varchar(50) NOT NULL,
      `icon_path` varchar(255) DEFAULT NULL,
      `promo_id` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $pdo->exec($sql);

    echo "Migration successful: vpn_profiles table created.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
