<?php
// Simple Cloud Server Configuration
// Store files directly on your cloud server

// Include common file extensions configuration
require_once 'file_extensions.php';

// Cloud Server Settings
define('CLOUD_STORAGE_ENABLED', true);
define('CLOUD_SERVER_PATH', __DIR__ . '/files/'); // Path on your cloud server
define('CLOUD_SERVER_URL', 'https://ppsu.thedevsecops.pro/files/'); // Public URL to access files

// File Settings
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('USE_TIMESTAMP', true);
define('USE_RANDOM_SUFFIX', true);
define('ORGANIZE_BY_DATE', true);

// Security Settings - Use common configuration
define('ALLOWED_EXTENSIONS', ALL_ALLOWED_EXTENSIONS);
define('CREATE_THUMBNAILS', true); // Set to true for images
define('LOG_UPLOADS', true);
?>
