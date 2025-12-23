<?php
try {
    // Add new columns to promos table if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM `promos`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('carrier', $columns)) {
        $pdo->exec('ALTER TABLE promos ADD COLUMN carrier VARCHAR(255)');
    }
    if (!in_array('config_text', $columns)) {
        $pdo->exec('ALTER TABLE promos ADD COLUMN config_text TEXT');
    }
    if (!in_array('is_active', $columns)) {
        $pdo->exec('ALTER TABLE promos ADD COLUMN is_active BOOLEAN');
    }

// Check if the configurations table exists before proceeding
$stmt = $pdo->query("SHOW TABLES LIKE 'configurations'");
if ($stmt->rowCount() > 0) {
    // Copy data from configurations to promos
    $stmt = $pdo->query("SELECT * FROM configurations");
    $configurations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($configurations as $config) {
        $sql = 'INSERT INTO promos (promo_name, icon_promo_path, carrier, config_text, is_active) VALUES (:promo_name, :icon_promo_path, :carrier, :config_text, :is_active)';
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':promo_name', $config['name'], PDO::PARAM_STR);
            $stmt->bindValue(':icon_promo_path', 'assets/promo/default.png', PDO::PARAM_STR); // Set a default icon
            $stmt->bindParam(':carrier', $config['carrier'], PDO::PARAM_STR);
            $stmt->bindParam(':config_text', $config['config_text'], PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $config['is_active'], PDO::PARAM_INT);
            $stmt->execute();
        }
        }

    // Drop the configurations table
    $pdo->exec('DROP TABLE configurations');
    }

    echo "Data migration completed successfully!";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
unset($sql);
?>