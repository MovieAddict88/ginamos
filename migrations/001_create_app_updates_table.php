<?php
// Migration to create the app_updates table
$sql = "CREATE TABLE IF NOT EXISTS app_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_code INT NOT NULL,
    version_name VARCHAR(255) NOT NULL,
    apk_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
?>
