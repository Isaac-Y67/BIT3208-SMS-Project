<?php
$host     = "localhost";
$dbname   = "sms_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2 style='color:green;'>✅ Database Connected Successfully!</h2>";
    echo "<p>Connected to: <strong>$dbname</strong></p>";
    echo "<p>Server: <strong>$host</strong></p>";
    echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>❌ Connection Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>