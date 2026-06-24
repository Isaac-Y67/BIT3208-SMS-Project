<?php
// ================================================
// File: modules/students/toggle.php
// Purpose: Activate or deactivate a student
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireRole('admin');

// Get student ID from URL
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL .
           '/modules/students/list.php');
    exit;
}

// Get current student status
$stmt = $pdo->prepare("
    SELECT s.*, u.id as user_id
    FROM students s
    JOIN users u ON u.id = s.user_id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('danger', 'Student not found.');
    header('Location: ' . BASE_URL .
           '/modules/students/list.php');
    exit;
}

// Toggle the status
$newStatus = $student['status'] === 'active'
           ? 'inactive'
           : 'active';

// Update both students and users tables
$pdo->beginTransaction();
try {
    // Update students table
    $stmt = $pdo->prepare("
        UPDATE students
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $id]);

    // Update users table
    $stmt = $pdo->prepare("
        UPDATE users
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $student['user_id']]);

    $pdo->commit();

    $action = $newStatus === 'active'
            ? 'activated' : 'deactivated';
    setFlash('success',
        'Student ' . $student['first_name'] .
        ' ' . $student['last_name'] .
        ' has been ' . $action . ' successfully.');

} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', 'Action failed. Try again.');
}

header('Location: ' . BASE_URL .
       '/modules/students/list.php');
exit;