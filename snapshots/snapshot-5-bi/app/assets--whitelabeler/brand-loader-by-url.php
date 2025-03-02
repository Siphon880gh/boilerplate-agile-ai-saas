<?php
// If ?co=BRAND is set, then set the brand in the session

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

if(isset($_GET['co'])) {
    $brand = $_GET['co'];
    $brand = urldecode($_GET['co']);

    $brand = strtolower($brand);
    $brand = preg_replace("/[^a-z0-9]/", "", $brand);

    $_SESSION["co"] = $brand;
} else {
    // Moved `unset($_SESSION["co"]);` to user logging out -> uninit-session.php
    // because
    /*
    location ~ ^/co/(.+)/?$ {  
        rewrite ^/co/(.+)/?$ /?co=$1 permanent;
    }
    */
    /* User visits:
    https://YOUR_DOMAIN/co/BRAND
    http://localhost:8080/app-auth-landing/?co=BRAND
    */

    // WOULD'VE THIS
    // unset($_SESSION["co"]);
    // OR THAT but /co/ not cooperating because I dont want to set all iframes and assets to base url
    // $requestUri = $_SERVER['REQUEST_URI'];
    // if (strpos($requestUri, "/co/") !== false) {
    //     unset($_SESSION["co"]);
    // }
    
}
?>