<?php
// migrations/20240804_create_profile_promos_table.php
// This is a corrected and idempotent version of the migration.

// Ensure tables are InnoDB for transactions and foreign keys
$pdo->exec("ALTER TABLE vpn_profiles ENGINE=InnoDB;");
$pdo->exec("ALTER TABLE promos ENGINE=InnoDB;");

// Drop the foreign key constraint on promo_id if it exists, ignoring errors.
try {
    // The specific FK name from the user's error message
    $pdo->exec("ALTER TABLE vpn_profiles DROP FOREIGN KEY `vpn_profiles_ibfk_1`;");
} catch (PDOException $e) {
    // Ignore the error, as the foreign key likely doesn't exist.
}

// Check if the promo_id column exists in vpn_profiles and drop it.
$stmt = $pdo->query("SHOW COLUMNS FROM `vpn_profiles` LIKE 'promo_id'");
if ($stmt->rowCount() > 0) {
    // Also drop the index on promo_id if it exists
    $stmt_index = $pdo->query("SHOW INDEX FROM `vpn_profiles` WHERE Key_name = 'promo_id'");
    if($stmt_index->rowCount() > 0) {
        $pdo->exec("ALTER TABLE vpn_profiles DROP INDEX promo_id;");
    }
    $pdo->exec("ALTER TABLE vpn_profiles DROP COLUMN promo_id;");
}

// Create the join table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS profile_promos (
        profile_id INT NOT NULL,
        promo_id INT NOT NULL,
        PRIMARY KEY (profile_id, promo_id),
        FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (promo_id) REFERENCES promos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
");
