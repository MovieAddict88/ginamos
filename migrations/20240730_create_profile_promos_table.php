<?php

require_once 'db_config.php';

try {
    // Step 1: Create the junction table if it doesn't exist. This is safe to re-run.
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

    // Step 2: Dynamically find and drop the foreign key, and then drop the column.
    // This is the robust way to handle this, as the constraint name can vary.

    // First, check if the column `promo_id` still exists in the `vpn_profiles` table.
    $stmt = $pdo->query("SHOW COLUMNS FROM `vpn_profiles` LIKE 'promo_id'");
    if ($stmt->rowCount() > 0) {
        // If the column exists, find the name of the foreign key constraint.
        $dbName = $pdo->query('select database()')->fetchColumn();
        $fk_stmt = $pdo->prepare(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = :db_name
             AND TABLE_NAME = 'vpn_profiles'
             AND COLUMN_NAME = 'promo_id'
             AND REFERENCED_TABLE_NAME IS NOT NULL"
        );
        $fk_stmt->execute(['db_name' => $dbName]);
        $constraint = $fk_stmt->fetch(PDO::FETCH_ASSOC);

        // If a constraint was found, drop it.
        if ($constraint) {
            $constraintName = $constraint['CONSTRAINT_NAME'];
            $pdo->exec("ALTER TABLE vpn_profiles DROP FOREIGN KEY `{$constraintName}`;");
            echo "Foreign key '{$constraintName}' dropped successfully.<br>";
        } else {
            echo "No foreign key found for 'promo_id' column, skipping drop.<br>";
        }

        // Now, drop the column itself.
        $pdo->exec("ALTER TABLE vpn_profiles DROP COLUMN promo_id;");
        echo "Column 'promo_id' dropped from 'vpn_profiles' successfully.<br>";

    } else {
        echo "Column 'promo_id' does not exist in 'vpn_profiles', no action needed.<br>";
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
