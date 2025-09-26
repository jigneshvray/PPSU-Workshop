<?php
// SimpleCloudStorage.php - Handles file uploads to your own cloud server

require_once 'config.php';

class SimpleCloudStorage {
    private $cloudPath;
    private $cloudUrl;
    
    public function __construct() {
        $this->cloudPath = CLOUD_SERVER_PATH;
        $this->cloudUrl = CLOUD_SERVER_URL;
        
        // Create cloud storage directory if it doesn't exist
        $this->createCloudDirectory();
    }
    
    /**
     * Upload file to cloud server
     */
    public function uploadFile($file, $customPath = null) {
        if (!CLOUD_STORAGE_ENABLED) {
            throw new Exception('Cloud storage is not enabled');
        }
        
        try {
            // Validate file
            $this->validateFile($file);
            
            // Generate unique filename
            $fileName = $this->generateFileName($file['name'], $customPath);
            $filePath = $this->cloudPath . $fileName;
            
            // Create directory structure if needed
            $this->createDirectoryStructure($fileName);
            
            // Move uploaded file to cloud storage
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Set proper permissions
                chmod($filePath, 0644);
                
                // Log upload if enabled
                if (LOG_UPLOADS) {
                    $this->logUpload($file['name'], $fileName);
                }
                
                return [
                    'success' => true,
                    'url' => $this->cloudUrl . $fileName,
                    'file_path' => $fileName,
                    'provider' => 'cloud_server',
                    'file_size' => filesize($filePath)
                ];
            } else {
                throw new Exception('Failed to move uploaded file to cloud storage');
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Cloud upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File too large. Maximum size: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS));
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = ALL_ALLOWED_MIME_TYPES;
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception('Invalid file type detected');
        }
    }
    
    /**
     * Generate unique filename
     */
    private function generateFileName($originalName, $customPath = null) {
        $pathInfo = pathinfo($originalName);
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
        $baseName = $pathInfo['filename'];
        
        $fileName = $baseName;
        
        if (USE_TIMESTAMP) {
            $fileName .= '_' . date('Y-m-d_H-i-s');
        }
        
        if (USE_RANDOM_SUFFIX) {
            $fileName .= '_' . substr(md5(uniqid()), 0, 8);
        }
        
        $fileName .= $extension;
        
        if (ORGANIZE_BY_DATE) {
            $datePath = date('Y/m/d') . '/';
        } else {
            $datePath = '';
        }
        
        if ($customPath) {
            return $customPath . '/' . $fileName;
        }
        
        return $datePath . $fileName;
    }
    
    /**
     * Create cloud storage directory
     */
    private function createCloudDirectory() {
        if (!is_dir($this->cloudPath)) {
            // Try to create directory with different permissions
            if (!@mkdir($this->cloudPath, 0777, true)) {
                // If that fails, try with 755 permissions
                if (!@mkdir($this->cloudPath, 0777, true)) {
                    // If still fails, try creating parent directories first
                    $parentDir = dirname($this->cloudPath);
                    if (!is_dir($parentDir)) {
                        @mkdir($parentDir, 0777, true);
                    }
                    // Try one more time
                    if (!@mkdir($this->cloudPath, 0777, true)) {
                        error_log("Failed to create cloud storage directory: " . $this->cloudPath . " - " . error_get_last()['message']);
                        throw new Exception('Failed to create cloud storage directory: ' . $this->cloudPath . '. Please check directory permissions.');
                    }
                }
            }
        }
        
        // Create .htaccess for security
        $htaccessPath = $this->cloudPath . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "Options -Indexes\n";
            $htaccessContent .= "Deny from all\n";
            $htaccessContent .= "<FilesMatch \"\\.(" . getHtaccessExtensions() . ")$\">\n";
            $htaccessContent .= "    Allow from all\n";
            $htaccessContent .= "</FilesMatch>\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }
    
    /**
     * Create directory structure for organized storage
     */
    private function createDirectoryStructure($fileName) {
        $fullPath = $this->cloudPath . $fileName;
        $directory = dirname($fullPath);
        
        if (!is_dir($directory)) {
            // Try to create directory with different permissions
            if (!@mkdir($directory, 0777, true)) {
                if (!@mkdir($directory, 0777, true)) {
                    error_log("Failed to create directory structure: " . $directory . " - " . error_get_last()['message']);
                    throw new Exception('Failed to create directory structure: ' . $directory . '. Please check directory permissions.');
                }
            }
        }
    }
    
    /**
     * Log upload activity
     */
    private function logUpload($originalName, $storedName) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logFile = $this->cloudPath . 'upload_log.txt';
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Delete file from cloud storage
     */
    public function deleteFile($filePath) {
        $fullPath = $this->cloudPath . $filePath;
        
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                return ['success' => true, 'message' => 'File deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to delete file'];
            }
        } else {
            return ['success' => false, 'error' => 'File not found'];
        }
    }
    
    /**
     * List files in cloud storage
     */
    public function listFiles($directory = '') {
        $path = $this->cloudPath . $directory;
        $files = [];
        
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'path' => str_replace($this->cloudPath, '', $file->getPathname()),
                        'size' => $file->getSize(),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                        'url' => $this->cloudUrl . str_replace($this->cloudPath, '', $file->getPathname())
                    ];
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filePath) {
        $fullPath = $this->cloudPath . $filePath;
        
        if (file_exists($fullPath)) {
            return [
                'exists' => true,
                'size' => filesize($fullPath),
                'modified' => date('Y-m-d H:i:s', filemtime($fullPath)),
                'url' => $this->cloudUrl . $filePath
            ];
        } else {
            return ['exists' => false];
        }
    }
}
?>

