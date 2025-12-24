<?php

require_once 'db_config.php';

try {
    $pdo->exec("
        CREATE TABLE profile_promos (
            profile_id INT NOT NULL,
            promo_id INT NOT NULL,
            PRIMARY KEY (profile_id, promo_id),
            FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (promo_id) REFERENCES promos(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        ALTER TABLE vpn_profiles
        DROP FOREIGN KEY vpn_profiles_ibfk_1;
    ");

    $pdo->exec("
        ALTER TABLE vpn_profiles
        DROP COLUMN promo_id;
    ");

    echo "Migration to create 'profile_promos' table and remove 'promo_id' from 'vpn_profiles' successful.\n";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
