<?php
require_once __DIR__ . '/../db_config.php';

try {
    // Create the profile_promos table
    $sql = "
    CREATE TABLE IF NOT EXISTS `profile_promos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `profile_id` INT NOT NULL,
        `promo_id` INT NOT NULL,
        FOREIGN KEY (`profile_id`) REFERENCES `vpn_profiles`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`promo_id`) REFERENCES `promos`(`id`) ON DELETE CASCADE
    )
    ";
    $pdo->exec($sql);

    // Check if the promo_id column exists before trying to drop it
    $stmt = $pdo->query("SHOW COLUMNS FROM `vpn_profiles` LIKE 'promo_id'");
    if ($stmt->rowCount() > 0) {
        // First, copy existing promo associations
        $sql = "INSERT INTO profile_promos (profile_id, promo_id) SELECT id, promo_id FROM vpn_profiles WHERE promo_id IS NOT NULL";
        $pdo->exec($sql);

        // Before dropping the column, check for and drop the foreign key constraint if it exists.
        // The constraint name might vary, so we need to find it first.
        $stmt = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vpn_profiles' AND COLUMN_NAME = 'promo_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        $constraint = $stmt->fetchColumn();

        if ($constraint) {
            $pdo->exec("ALTER TABLE `vpn_profiles` DROP FOREIGN KEY `{$constraint}`");
        }

        // Now, drop the column
        $sql = "ALTER TABLE `vpn_profiles` DROP COLUMN `promo_id`";
        $pdo->exec($sql);
    }

    echo "Migration successful.\n";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
