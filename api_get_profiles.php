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

    // Check if promo_id is set in the POST request
    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
        $promo_id = $_POST['promo_id'];

        // Prepare a select statement to retrieve profiles and all their associated promo configurations
        $sql = "
            SELECT
                p.id,
                p.name AS profile_name,
                p.ovpn_config,
                p.type AS profile_type,
                p.icon_path,
                GROUP_CONCAT(pr.id) as promo_ids,
                GROUP_CONCAT(pr.promo_name) as promo_names,
                GROUP_CONCAT(pr.config_text SEPARATOR '|||') as promo_configs
            FROM
                vpn_profiles p
            JOIN
                profile_promos pp ON p.id = pp.profile_id
            JOIN
                promos pr ON pp.promo_id = pr.id
            WHERE
                p.id IN (SELECT profile_id FROM profile_promos WHERE promo_id = :promo_id)
            GROUP BY
                p.id, p.name, p.ovpn_config, p.type, p.icon_path
            ORDER BY
                p.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':promo_id', $promo_id, PDO::PARAM_INT);
        $stmt->execute();
        $profiles_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $profiles = [];
        $base_url = get_base_url();

        foreach ($profiles_raw as $profile_raw) {
            $promo_ids_arr = explode(',', $profile_raw['promo_ids']);
            $promo_names_arr = explode(',', $profile_raw['promo_names']);
            $promo_configs_arr = explode('|||', $profile_raw['promo_configs']);

            $promos_list = [];
            for ($i = 0; $i < count($promo_ids_arr); $i++) {
                $promos_list[] = [
                    'promo_id' => $promo_ids_arr[$i],
                    'promo_name' => $promo_names_arr[$i],
                    'profile_content' => $profile_raw['ovpn_config'] . "\n" . $promo_configs_arr[$i],
                ];
            }

            $profiles[] = [
                'id' => $profile_raw['id'],
                'profile_name' => $profile_raw['profile_name'],
                'profile_type' => $profile_raw['profile_type'],
                'icon_path' => !empty($profile_raw['icon_path']) ? $base_url . $profile_raw['icon_path'] : null,
                'ping' => rand(20, 200),
                'signal_strength' => rand(30, 100),
                'promos' => $promos_list,
            ];
        }
    } else {
        // If no promo_id is provided, return an empty list of profiles.
        $profiles = [];
    }

    // Set the content type header to application/json
    header('Content-Type: application/json');

    // Output the profiles as a JSON encoded string
    echo json_encode(['status' => 'success', 'profiles' => $profiles]);

} catch (PDOException $e) {
    // Handle potential database errors
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
