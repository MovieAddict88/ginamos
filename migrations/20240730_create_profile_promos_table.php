<?php

require_once 'db_config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS profile_promos (
            profile_id INT NOT NULL,
            promo_id INT NOT NULL,
            PRIMARY KEY (profile_id, promo_id),
            FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (promo_id) REFERENCES promos(id) ON DELETE CASCADE
        );
    ");
    echo "Table 'profile_promos' checked/created successfully.<br>";

    // Check if the promo_id column exists in vpn_profiles before trying to drop it
    $stmt = $pdo->query("SHOW COLUMNS FROM `vpn_profiles` LIKE 'promo_id'");
    if ($stmt->rowCount() > 0) {
        // The column exists, so we can proceed to drop the foreign key and the column
        $pdo->exec("ALTER TABLE vpn_profiles DROP FOREIGN KEY vpn_profiles_ibfk_1;");
        echo "Foreign key 'vpn_profiles_ibfk_1' dropped successfully.<br>";

        $pdo->exec("ALTER TABLE vpn_profiles DROP COLUMN promo_id;");
        echo "Column 'promo_id' dropped from 'vpn_profiles' successfully.<br>";
    } else {
        echo "Column 'promo_id' does not exist in 'vpn_profiles', skipping drop.<br>";
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
