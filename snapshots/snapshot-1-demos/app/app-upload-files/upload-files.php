<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set PHP configuration for file uploads
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('memory_limit', '100M'); // Adjust if needed

// Create upload directory if it doesn't exist
$uploadDir = "../users/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Log request data
// $logFile = "../users/debug-input.log";
// $logMessage = "Request received at " . date('Y-m-d H:i:s') . "\n";
// $logMessage .= "POST data: " . print_r($_POST, true) . "\n";
// $logMessage .= "FILES data: " . print_r($_FILES, true) . "\n";

// if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
//     echo json_encode(["error" => "Failed to write to log file."]);
//     exit;
// }

$response = [
    'files' => [],
    'errors' => []
];

// Extract metadata from POST data
$userId = $_POST['userId'] ?? 'unknown_user';
$appId = $_POST['appId'] ?? 'unknown_app';
$caseId = $_POST['caseId'] ?? 'unknown_case';

// Process uploaded files
$index = 0;
foreach ($_FILES as $key => $fileInfo) {
    if ($fileInfo['error'] === UPLOAD_ERR_OK) {
        // Extract file extension
        $fileExt = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        
        // Check if this is a URL text file
        $isUrlFile = strpos($fileInfo['name'], 'url-') === 0;
        
        // Construct new filename with index
        $newFileName = $isUrlFile ? 
            "file-{$index}-a{$appId}-{$caseId}-{$userId}.url.txt" : 
            "file-{$index}-a{$appId}-{$caseId}-{$userId}." . $fileExt;
        
        $filePath = $uploadDir . $newFileName;
        
        // Move uploaded file to destination
        if (move_uploaded_file($fileInfo['tmp_name'], $filePath)) {
            $response['files'][] = [
                'path' => $filePath,
                'index' => $index,
                'isUrl' => $isUrlFile
            ];
        } else {
            $response['errors'][] = "Failed to move uploaded file: " . $fileInfo['name'];
        }
    } else {
        $response['errors'][] = "Error with file: " . $fileInfo['name'] . " - Error code: " . $fileInfo['error'];
    }
    $index++; // Increment index for each file
}

// Return response
header('Content-Type: application/json');
echo json_encode($response);
?>
