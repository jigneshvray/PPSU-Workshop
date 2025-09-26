<?php
// upload.php - Simple Cloud Server Upload Handler

// Disable error reporting to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'CloudStorageManager.php';

// Enable CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['resume'])) {
        http_response_code(400);
        die(json_encode(['error' => 'No file uploaded.']));
    }

    $file = $_FILES['resume'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        die(json_encode(['error' => 'Upload error code: ' . $file['error']]));
    }

    try {
        // Initialize simple cloud storage
        $cloudStorage = new SimpleCloudStorage();
        
        // Upload to cloud server
        $result = $cloudStorage->uploadFile($file);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully to cloud server',
                'file_name' => htmlspecialchars($file['name']),
                'cloud_url' => $result['url'],
                'file_path' => $result['file_path'],
                'file_size' => $result['file_size'],
                'provider' => 'cloud_server'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $result['error']]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Cloud upload failed: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method allowed']);
}
?>
