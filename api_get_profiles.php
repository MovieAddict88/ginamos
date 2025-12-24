<?php
// api_get_profiles.php

// Include the database configuration
require_once 'db_config.php';
require_once 'utils.php';

// Check if the login_code is set
if (!isset($_POST['login_code'])) {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Login code is required.']);
    exit;
}

try {
    $login_code = $_POST['login_code'];

    // Validate the login_code
    $sql = 'SELECT id, banned FROM users WHERE login_code = :login_code';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':login_code', $login_code, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        header('Content-Type: application/json');
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid login code.']);
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user['banned']) {
        header('Content-Type: application/json');
        http_response_code(403); // Forbidden
        echo json_encode(['status' => 'error', 'message' => 'User is banned.']);
        exit;
    }

    // Prepare a select statement to retrieve profiles and their associated promo configurations
    $sql = "
        SELECT
            p.id,
            p.name AS profile_name,
            p.ovpn_config,
            pr.config_text,
            p.type as profile_type,
            p.icon_path,
            pr.id as promo_id,
            pr.promo_name
        FROM
            vpn_profiles p
        JOIN
            profile_promos pp ON p.id = pp.profile_id
        JOIN
            promos pr ON pp.promo_id = pr.id
        ORDER BY
            p.name ASC, pr.promo_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $profiles = [];
    foreach ($results as $row) {
        $profile_id = $row['id'];
        if (!isset($profiles[$profile_id])) {
            $profiles[$profile_id] = [
                'id' => $profile_id,
                'profile_name' => $row['profile_name'],
                'profile_type' => $row['profile_type'],
                'icon_path' => $row['icon_path'],
                'promos' => [],
            ];
        }

        $profiles[$profile_id]['promos'][] = [
            'promo_id' => $row['promo_id'],
            'promo_name' => $row['promo_name'],
            'profile_content' => $row['ovpn_config'] . "\n" . $row['config_text'],
        ];
    }

    $base_url = get_base_url();
    $response_profiles = [];
    foreach ($profiles as $profile) {
        if (!empty($profile['icon_path'])) {
            $profile['icon_path'] = $base_url . $profile['icon_path'];
        }
        // Simulate ping for each profile
        $profile['ping'] = rand(20, 200);
        $profile['signal_strength'] = rand(30, 100);
        $response_profiles[] = $profile;
    }

    // Set the content type header to application/json
    header('Content-Type: application/json');

    // Output the profiles as a JSON encoded string
    echo json_encode(['status' => 'success', 'profiles' => $response_profiles]);

} catch (PDOException $e) {
    // Handle potential database errors
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
