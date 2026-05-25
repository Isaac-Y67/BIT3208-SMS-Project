<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || 
    $_SESSION['user_role'] !== 'student') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body text-center p-5">
            <i class="bi bi-mortarboard text-warning" 
               style="font-size:60px;"></i>
            <h2 class="mt-3" style="color:#1a237e;">
                Student Dashboard
            </h2>
            <p class="text-muted">
                Welcome, 
                <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>!
            </p>
            <p class="text-muted">
                Role: 
                <span class="badge bg-warning text-dark">
                    <?= strtoupper($_SESSION['user_role']) ?>
                </span>
            </p>
            <p class="text-success">
                ✅ Login successful! Full dashboard coming in Week 4.
            </p>
            <a href="../logout.php" class="btn btn-danger mt-3">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </div>
    </div>
</div>
</body>
</html>