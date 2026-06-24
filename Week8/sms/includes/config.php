<?php
// ================================================
// File: C:\xampp\htdocs\sms\includes\config.php
// Purpose: App-wide settings and constants
// Included FIRST in every PHP page
// ================================================

// Show errors during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Base URL — used to build links across all pages
define('BASE_URL',      'http://localhost/sms');

// App details
define('APP_NAME',      'Student Management System');
define('APP_SHORT',     'SMS');
define('ACADEMIC_YEAR', '2024-2025');

// Upload settings
define('UPLOAD_PATH',   __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

// Pagination
define('ROWS_PER_PAGE', 15);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}