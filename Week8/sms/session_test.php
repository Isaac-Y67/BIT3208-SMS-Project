<?php
// ================================================
// File: session_test.php
// Purpose: Display current session variables
// TEMPORARY FILE — delete after taking screenshot
// ================================================
require_once 'includes/config.php';

echo "<h2 style='color:#1a237e; font-family:Arial;'>Current Session Variables</h2>";

if (empty($_SESSION)) {
    echo "<p style='color:red;'>No active session. Please login first.</p>";
} else {
    echo "<pre style='background:#f5f5f5; padding:20px; 
                       border-radius:8px; font-size:14px;'>";
    print_r($_SESSION);
    echo "</pre>";
}
?>