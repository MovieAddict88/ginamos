<?php
// test_api.php

require_once 'db_config.php';

// Create a test user
$login_code = 'test_user_' . uniqid();
$stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, address, contact_number, login_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['testuser', 'password', 'Test', 'User', '123 Test St', '555-1234', $login_code]);
$user_id = $pdo->lastInsertId();

// Create a test promo
$stmt = $pdo->prepare("INSERT INTO promos (carrier, promo_name, config_text) VALUES (?, ?, ?)");
$stmt->execute(['Test Carrier', 'Test Promo', 'test_config']);
$promo_id = $pdo->lastInsertId();

// Create a test profile
$stmt = $pdo->prepare("INSERT INTO vpn_profiles (name, ovpn_config, type) VALUES (?, ?, ?)");
$stmt->execute(['Test Profile', 'base_config', 'Premium']);
$profile_id = $pdo->lastInsertId();

// Associate the promo with the profile
$stmt = $pdo->prepare("INSERT INTO profile_promos (profile_id, promo_id) VALUES (?, ?)");
$stmt->execute([$profile_id, $promo_id]);

// Simulate the API call
$_POST['login_code'] = $login_code;
$_POST['promo_id'] = $promo_id;

echo "Running test for api_get_profiles.php...\n";

// Capture the output of the API script
ob_start();
include 'api_get_profiles.php';
$output = ob_get_clean();

// Decode the JSON response
$response = json_decode($output, true);

// Verify the response
if ($response && $response['status'] == 'success') {
    if (!empty($response['profiles'])) {
        $profile = $response['profiles'][0];
        if ($profile['id'] == $profile_id && !empty($profile['promos'])) {
            $promo = $profile['promos'][0];
            if ($promo['promo_id'] == $promo_id && $promo['profile_content'] == "base_config\ntest_config") {
                echo "Test PASSED!\n";
            } else {
                echo "Test FAILED: Promo data is incorrect.\n";
            }
        } else {
            echo "Test FAILED: Profile data is incorrect.\n";
        }
    } else {
        echo "Test FAILED: No profiles returned.\n";
    }
} else {
    echo "Test FAILED: API call was not successful.\n";
    echo "Response: " . $output . "\n";
}

// Clean up the test data
$pdo->exec("DELETE FROM users WHERE id = $user_id");
$pdo->exec("DELETE FROM promos WHERE id = $promo_id");
$pdo->exec("DELETE FROM vpn_profiles WHERE id = $profile_id");

?>
