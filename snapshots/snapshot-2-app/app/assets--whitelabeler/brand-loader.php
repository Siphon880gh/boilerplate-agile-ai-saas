<?php
// optionally import $up=1 from iframe

/* 
    Relative:
    PHP Includes is from perspective of /assets
    Link href and Script src is from perspective of /
*/ 
?>
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

$prefix = "";
// Check if $up is set and is a numeric value
if (isset($up) && is_numeric($up)) {
    // Cast $up to an integer for safety
    $up = (int)$up;
    
    // Create the prefix of "../" repeated $up times
    $prefix = str_repeat("../", $up);
}

$isDefaultBrand = false; // to override
if(isset($_SESSION['co'])) {
    $brand = $_SESSION['co'];
    if($brand == "partner1") {
        echo "<link href='$prefix./assets--whitelabeler/branding-partner1/common-partner1.css' rel='stylesheet'>";
        echo "<script src='$prefix./assets--whitelabeler/branding-partner1/common-partner1.js'></script>";

        include("$prefix./assets--whitelabeler/branding-partner1/common-partner1.php");

    } else {
        $isDefaultBrand = true;
    }    
} else {
    $isDefaultBrand = true;
}

if($isDefaultBrand) {
    echo "<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>";
    echo "<link href='$prefix./assets--whitelabeler/branding-default/common-default.css' rel='stylesheet'>";
    echo "<script src='$prefix./assets--whitelabeler/branding-default/common-default.js'></script>";

    include("$prefix./assets--whitelabeler/branding-default/common-default.php");
}