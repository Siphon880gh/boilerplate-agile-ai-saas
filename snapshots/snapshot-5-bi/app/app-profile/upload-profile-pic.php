<?php
header('Content-Type: application/json');

try {
    if (!isset($_FILES['profile_picture']) || !isset($_POST['userId']) || !isset($_POST['appId'])) {
        throw new Exception('Missing required fields');
    }

    $userId = $_POST['userId'];
    $appId = $_POST['appId'];
    $uploadDir = '../users/';

    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = "user-profile-pic-ar916-{$appId}-{$userId}.jpg";
    $targetPath = $uploadDir . $fileName;

    // Remove old file if it exists
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile picture uploaded successfully',
            'path' => $targetPath
        ]);
    } else {
        throw new Exception('Failed to move uploaded file');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
