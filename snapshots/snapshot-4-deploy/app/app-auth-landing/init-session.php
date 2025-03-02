<?php
// Start the session
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$app_config_path = "../app.APP_ABBREV.config.json";

try {
    // Attempt to start the session only if no session is active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Confirm the session is active
    if (session_status() != PHP_SESSION_ACTIVE) {
        throw new Exception("Failed to start session. Check your PHP configuration using php_ini_loaded_file().");
    }
} catch (Exception $e) {
    // Log the error or display a user-friendly message
    error_log("Session Error: " . $e->getMessage());
    echo "An error occurred while starting the session. Please contact the administrator.";
}


// Check if the form was submitted and the user_id is set in the POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    // Get the user_id from the POST request
    $user_id = $_POST['user_id'];
    
    // Save the user_id as a session variable
    $_SESSION['user_id'] = $user_id;

    // Read app_config_path for "app_abbrev" value
    $app_abbrev = json_decode(file_get_contents($app_config_path))->app_abbrev;
    $_SESSION['app_id'] = $app_abbrev;
    
    $brands = ['partner1'];
    if (isset($_POST['co']) && strlen($_POST['co']) > 0) {

        $brand = $_POST['co'];
        $brand = strtolower($brand);
        $brand = preg_replace("/[^a-z0-9]/", "", $brand);

        if (!in_array($brand, $brands)) {
            return;
        }
        if(!isset($_SESSION['co'])) {
            $_SESSION['co'] = $_POST['co'];
            // header("Location: ?co=" . $_POST['co']);
            die(json_encode(['error'=>0, 'redirectForWhitelabelingAgency'=>"?co=" . $_POST['co'], 'from'=>'init-session.php', 'status' => 'success', 'message' => 'App ID and User ID saved to \$_SESSION, however we do not know if truly succces unless tested in another php file. User Id and App Id: ' . $_SESSION['user_id'] . ", " . $_SESSION['app_id']]));
        }
    }
    
    // Respond with a success message
    die(json_encode(['error'=>0, 'from'=>'init-session.php', 'status' => 'success', 'message' => 'App ID and User ID saved to \$_SESSION, however we do not know if truly succces unless tested in another php file. User Id and App Id: ' . $_SESSION['user_id'] . ", " . $_SESSION['app_id']]));
} else {
    die(json_encode(['error'=>1, 'from'=>'init-session.php', 'status' => 'success', 'error_desc' => 'App ID and User ID NOT saved to \$_SESSION as POST request and POST variable user_id not found']));
}
// ?>
