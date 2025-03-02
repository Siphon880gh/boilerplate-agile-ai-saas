<?php
// imported `$finalHost` by here including app root's `assets/common.php`

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("../assets/common.php");

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

// We did not want to expose the URL to get the access information so it's in PHP
$params = [];
if(isset($_SESSION['app_id']) && isset($_SESSION['user_id'])) {
    $params = [
        'appId' => $_SESSION['app_id'],
        'userId' => $_SESSION['user_id']
    ];
    // var_dump($params);
} else {
    echo "ERROR - Server session variables not setting after logging in, so unable to pull credits at PHP connecting to Python API";
    echo "<script>document.location.reload();</script>";
    die("");
}
$data = false; // Will be replaced

// Convert the parameters array to a query string
$queryString = http_build_query($params);

// The URL to call
// https://wengindustries.com:5001/profile/credits/access-page?appId=APP_ABBREV&userId=01234abcd555555555ef6789
$url = "$finalHost/profile/credits/access-page?" . $queryString;
// echo $url;

// Initialize cURL
$ch = curl_init();

// SSL or not in PHP cURL
require '../vendor/autoload.php';
use Dotenv\Dotenv; // Must be outside of if statement
if(isset($phpCurlSSL) && $phpCurlSSL == 1) {
    $dotenv = Dotenv::createImmutable(__DIR__. "/..", '.env.local');
    $dotenv->load();
    $SSL_CERT_PATH = $_ENV['SSL_CERT_PATH'] ?? '';
    $SSL_KEY_PATH = $_ENV['SSL_KEY_PATH'] ?? '';
    $SSL_CAINFO_PATH = $_ENV['SSL_CAINFO_PATH'] ?? '';

    if(strlen($SSL_CERT_PATH) > 0) {
        curl_setopt($ch, CURLOPT_SSLCERT, $SSL_CERT_PATH);
    }
    if(strlen($SSL_KEY_PATH) > 0) {
        curl_setopt($ch, CURLOPT_SSLKEY, $SSL_KEY_PATH);
    }
    if(strlen($SSL_CAINFO_PATH) > 0) {
        curl_setopt($ch, CURLOPT_CAINFO, $SSL_CAINFO_PATH);
    }
} else {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
}

// Set the URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request
$response = curl_exec($ch);

// Check for cURL errors
if(curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // Decode the JSON response
    $data_ = json_decode($response, true);
    
    // Check if JSON decoding was successful
    if (json_last_error() === JSON_ERROR_NONE) {
        $data = json_encode($data_);
    } else {
        //echo 'JSON decode error: ' . json_last_error_msg();
    }
}

// Close the cURL session
curl_close($ch);

?>
