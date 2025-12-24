<?php
// migrations/20240804_create_profile_promos_table.php

// First, convert the vpn_profiles table to InnoDB to support foreign keys and transactions
$pdo->exec("ALTER TABLE vpn_profiles ENGINE=InnoDB;");

// Now, create the join table with foreign key constraints
$pdo->exec("
    CREATE TABLE profile_promos (
        profile_id INT NOT NULL,
        promo_id INT NOT NULL,
        PRIMARY KEY (profile_id, promo_id),
        FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (promo_id) REFERENCES promos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
");

// Finally, drop the old promo_id column from the vpn_profiles table
$pdo->exec("
    ALTER TABLE vpn_profiles DROP COLUMN promo_id;
");
