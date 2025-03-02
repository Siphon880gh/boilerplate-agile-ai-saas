<?php
// Start the session
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Deprecated hard session destruction because we may keep session for branded agency

// Clear all session variables
// $_SESSION = [];

// If it's desired to kill the session completely, delete the session cookie.
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(
//         session_name(),
//         '',
//         time() - 42000,
//         $params["path"],
//         $params["domain"],
//         $params["secure"],
//         $params["httponly"]
//     );
// }

// Destroy the session
// session_destroy();


unset($_SESSION['user_id']);
unset($_SESSION['app_id']);
unset($_SESSION["co"]);

die(json_encode(['error'=>0, 'from'=>'uninit-session.php', 'status' => 'success', 'error_desc' => 'App ID and User ID made sure to be cleared off PHP session.']));
// ?>
