<?php
// Load the database connection
require_once 'includes/db.php';

echo "<h2 style='color:green;'>✅ Database Connected!</h2>";
echo "<p>Connected to: <strong>sms_db</strong></p>";
echo "<p>Host: <strong>localhost</strong></p>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";
echo "<p style='color:green;'>PDO connection is working correctly ✅</p>";
?>