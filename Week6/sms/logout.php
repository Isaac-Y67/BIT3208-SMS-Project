<?php
// ================================================
// File: C:\xampp\htdocs\sms\logout.php
// Purpose: Destroy session and redirect to login
// ================================================
require_once 'includes/config.php';

// Destroy everything in the session
$_SESSION = [];
session_destroy();

// Redirect to login page
header('Location: index.php');
exit;