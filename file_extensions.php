<?php
// Common file extensions configuration
// This file contains all file extension definitions used across the application

// Document extensions
define('DOCUMENT_EXTENSIONS', ['pdf', 'doc', 'docx', 'txt', 'csv']);

// Image extensions
define('IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Executable extensions
define('EXECUTABLE_EXTENSIONS', ['exe', 'msi', 'deb', 'rpm', 'app', 'dmg', 'pkg', 'run', 'sh', 'bat', 'cmd']);

// Script extensions
define('SCRIPT_EXTENSIONS', ['php', 'php3', 'php4', 'php5', 'phtml']);

// All allowed extensions (combines all above)
define('ALL_ALLOWED_EXTENSIONS', array_merge(
    DOCUMENT_EXTENSIONS,
    IMAGE_EXTENSIONS,
    EXECUTABLE_EXTENSIONS,
    SCRIPT_EXTENSIONS
));

// MIME types for different file categories
define('DOCUMENT_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
    'text/csv'
]);

define('IMAGE_MIME_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif'
]);

define('EXECUTABLE_MIME_TYPES', [
    'application/x-msdownload',
    'application/x-msi',
    'application/vnd.debian.binary-package',
    'application/x-rpm',
    'application/x-apple-diskimage',
    'application/x-executable',
    'application/x-sh',
    'application/x-shellscript',
    'text/x-shellscript',
    'application/x-bat',
    'application/x-msdos-program'
]);

define('SCRIPT_MIME_TYPES', [
    'application/x-httpd-php',
    'application/x-php',
    'text/x-php',
    'text/php',
    'application/php'
]);

// All allowed MIME types
define('ALL_ALLOWED_MIME_TYPES', array_merge(
    DOCUMENT_MIME_TYPES,
    IMAGE_MIME_TYPES,
    EXECUTABLE_MIME_TYPES,
    SCRIPT_MIME_TYPES
));

// Function to get extensions for .htaccess FilesMatch
function getHtaccessExtensions() {
    return implode('|', array_map(function($ext) {
        return preg_quote($ext, '/');
    }, ALL_ALLOWED_EXTENSIONS));
}

// Function to get extensions for HTML accept attribute
function getHtmlAcceptExtensions() {
    return '.' . implode(',.', ALL_ALLOWED_EXTENSIONS);
}

// Function to get human-readable extension list
function getHumanReadableExtensions() {
    return strtoupper(implode(', ', ALL_ALLOWED_EXTENSIONS));
}
?>
