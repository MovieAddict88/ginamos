<?php
// migrations/20240805_create_profile_promos_table.php

require_once __DIR__ . '/../db_config.php';

try {
    // Create the profile_promos table
    $sql = "
    CREATE TABLE IF NOT EXISTS profile_promos (
        profile_id INT NOT NULL,
        promo_id INT NOT NULL,
        PRIMARY KEY (profile_id, promo_id),
        FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (promo_id) REFERENCES promos(id) ON DELETE CASCADE
    )
    ";
    $pdo->exec($sql);

    // Migrate existing data from vpn_profiles to profile_promos
    $sql = "
    INSERT INTO profile_promos (profile_id, promo_id)
    SELECT id, promo_id FROM vpn_profiles WHERE promo_id IS NOT NULL
    ";
    $pdo->exec($sql);

    // Remove the promo_id column from vpn_profiles
    $sql = "
    ALTER TABLE vpn_profiles
    DROP COLUMN promo_id
    ";
    $pdo->exec($sql);

    echo "Migration successful: profile_promos table created, data migrated, and promo_id column dropped.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
