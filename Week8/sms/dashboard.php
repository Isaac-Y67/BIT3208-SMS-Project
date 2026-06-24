<?php
// ================================================
// File: C:\xampp\htdocs\sms\dashboard.php
// Purpose: Role-based redirect hub after login
// ================================================
require_once 'includes/config.php';
require_once 'includes/db.php';

// If not logged in send back to login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Read the role from session
$role = $_SESSION['user_role'];

// Redirect to correct dashboard based on role
switch ($role) {
    case 'admin':
        header('Location: dashboards/admin.php');
        break;
    case 'teacher':
        header('Location: dashboards/teacher.php');
        break;
    case 'student':
        header('Location: dashboards/student.php');
        break;
    default:
        // Unknown role — log out and go back
        session_destroy();
        header('Location: index.php');
        break;
}
exit;