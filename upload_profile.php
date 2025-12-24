<?php
// Start session
session_start();

echo "Script start";

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Not logged in";
    //header('location: login.php');
    //exit;
}

// Include the database connection file
require_once 'db_config.php';

$upload_message = '';
$error_message = '';

// Check if a file was uploaded and a name was provided
if (isset($_FILES['profile_ovpn']) && isset($_POST['profile_type'])) {
    echo "File uploaded";
    $profile_type = trim($_POST['profile_type']);
    $promo_ids = isset($_POST['promo_ids']) ? $_POST['promo_ids'] : [];

    $file = $_FILES['profile_ovpn'];
    $file_name = $file['name'];
    $file_tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if ($file_ext == 'ovpn' && $file_size <= 1000000) {
        echo "File valid";
        $profile_name = pathinfo($file_name, PATHINFO_FILENAME);
        $ovpn_config = file_get_contents($file_tmp_name);

        $pdo->beginTransaction();
        try {
            $sql = 'INSERT INTO vpn_profiles (name, ovpn_config, type) VALUES (:name, :ovpn_config, :type)';
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(':name', $profile_name, PDO::PARAM_STR);
                $stmt->bindParam(':ovpn_config', $ovpn_config, PDO::PARAM_STR);
                $stmt->bindParam(':type', $profile_type, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    echo "Profile inserted";
                    $profile_id = $pdo->lastInsertId();
                    if (!empty($promo_ids) && is_array($promo_ids)) {
                        $sql_assoc = 'INSERT INTO profile_promos (profile_id, promo_id) VALUES (:profile_id, :promo_id)';
                        $stmt_assoc = $pdo->prepare($sql_assoc);
                        foreach ($promo_ids as $promo_id) {
                            $stmt_assoc->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
                            $stmt_assoc->bindParam(':promo_id', $promo_id, PDO::PARAM_INT);
                            $stmt_assoc->execute();
                        }
                    }
                    $pdo->commit();
                    $upload_message = 'Profile uploaded successfully.';
                } else {
                    $pdo->rollBack();
                    $error_message = 'Failed to upload profile.';
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Invalid file type or size. Please upload a .ovpn file smaller than 1MB.';
    }
}

include 'header.php';
?>

<div class="page-header">
    <h1>Upload Profile</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Upload new .ovpn profile</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($upload_message)) : ?>
            <div class="alert alert-info"><?php echo $upload_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="upload_profile.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_ovpn">Select .ovpn file to upload:</label>
                <input type="file" name="profile_ovpn" id="profile_ovpn" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="profile_type">Profile Type:</label>
                <select name="profile_type" id="profile_type" class="form-control">
                    <option value="Premium">Premium</option>
                    <option value="Freemium">Freemium</option>
                </select>
            </div>
            <div class="form-group">
                <label>Promos</label>
                <div class="promo-checkbox-group" style="height: 150px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 4px;">
                    <?php
                    $sql = 'SELECT id, promo_name FROM promos ORDER BY promo_name';
                    $promos = $pdo->query($sql)->fetchAll();
                    if (empty($promos)) {
                        echo '<p>No promos available.</p>';
                    } else {
                        foreach ($promos as $promo) {
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input" type="checkbox" name="promo_ids[]" value="' . $promo['id'] . '" id="promo_' . $promo['id'] . '">';
                            echo '<label class="form-check-label" for="promo_' . $promo['id'] . '">';
                            echo htmlspecialchars($promo['promo_name']);
                            echo '</label>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
