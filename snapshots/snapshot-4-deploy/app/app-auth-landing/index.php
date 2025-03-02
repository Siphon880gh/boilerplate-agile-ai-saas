<!-- Module type: PHP partial <root>/modals.php for <root>/inde.php -->
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Demo Layout</title>
  <!-- Include Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
  
<?php include("../assets/version-cache-bust.php");
echo <<<cbust_ipad
  <link href="../assets/common.css$v" rel="stylesheet">
  <script src="../assets/screens.js$v"></script>
cbust_ipad;
?>

  <?php $up=1; include("../assets--whitelabeler/brand-loader.php"); unset($up); ?>
</head>

<body class="text-gray-900 flex justify-center items-center min-h-screen">

<?php
if (isset($_SESSION['partial-auth-landing'])) {
  $html = $_SESSION['partial-auth-landing'];
  echo $html;
} else {
  echo '<div class="flex flex-row justify-center">ERROR - No branded or default HTML for partial-auth-landing found.</div>';
}
?>

<?php
echo <<<cbust_ipad
    <script src="assets/index.js$v"></script>
cbust_ipad;
?>
</body>
</html>