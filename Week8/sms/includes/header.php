<?php
// ================================================
// File: C:\xampp\htdocs\sms\includes\header.php
// Purpose: Shared sidebar + top navbar
// Included at top of every protected page
// ================================================

// $pageTitle must be set before including this file
$pageTitle = $pageTitle ?? 'Dashboard';

// Get first letter of name for avatar
$avatarLetter = strtoupper(substr(authName(), 0, 1));

// Get active announcements for this role
$role = authRole();
$annStmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM announcements 
    WHERE is_active = 1 
    AND (target_role = 'all' OR target_role = ?)
");
$annStmt->execute([$role]);
$annCount = $annStmt->fetch()['total'];

// Get flash message if any
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — <?= APP_NAME ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sms-wrapper">

<!-- ══ SIDEBAR ══════════════════════════════════ -->
<aside class="sms-sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="brand-text">
            <h5><?= APP_SHORT ?></h5>
            <small><?= ACADEMIC_YEAR ?></small>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <?php if ($role === 'admin'): ?>
        <!-- ── ADMIN MENU ── -->
        <div class="nav-section">Main</div>

        <a href="<?= BASE_URL ?>/dashboards/admin.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'admin.php') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">Management</div>

        <a href="<?= BASE_URL ?>/modules/students/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/students/') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Students
        </a>

        <a href="<?= BASE_URL ?>/modules/teachers/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/teachers/') ? 'active' : '' ?>">
            <i class="bi bi-person-workspace"></i> Teachers
        </a>

        <a href="<?= BASE_URL ?>/modules/courses/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/courses/') ? 'active' : '' ?>">
            <i class="bi bi-book"></i> Courses
        </a>

        <div class="nav-section">Academic</div>

        <a href="<?= BASE_URL ?>/modules/grades/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/grades/') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i> Grades
        </a>

        <a href="<?= BASE_URL ?>/modules/attendance/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/attendance/') ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> Attendance
        </a>

        <a href="<?= BASE_URL ?>/modules/announcements/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/announcements/') ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i> Announcements
            <?php if ($annCount > 0): ?>
            <span class="ms-auto badge bg-warning text-dark"
                  style="font-size:10px;">
                <?= $annCount ?>
            </span>
            <?php endif; ?>
        </a>

        <div class="nav-section">System</div>

        <a href="<?= BASE_URL ?>/admin/users.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i> Users
        </a>

        <a href="<?= BASE_URL ?>/admin/reports.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/reports') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports
        </a>

        <a href="<?= BASE_URL ?>/admin/settings.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> Settings
        </a>

        <?php elseif ($role === 'teacher'): ?>
        <!-- ── TEACHER MENU ── -->
        <div class="nav-section">Main</div>

        <a href="<?= BASE_URL ?>/dashboards/teacher.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'teacher.php') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">My Classes</div>

        <a href="<?= BASE_URL ?>/modules/courses/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/courses/') ? 'active' : '' ?>">
            <i class="bi bi-book"></i> My Courses
        </a>

        <a href="<?= BASE_URL ?>/modules/grades/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/grades/') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i> Grades
        </a>

        <a href="<?= BASE_URL ?>/modules/attendance/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/attendance/') ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> Attendance
        </a>

        <div class="nav-section">Communication</div>

        <a href="<?= BASE_URL ?>/modules/announcements/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/announcements/') ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i> Announcements
            <?php if ($annCount > 0): ?>
            <span class="ms-auto badge bg-warning text-dark"
                  style="font-size:10px;">
                <?= $annCount ?>
            </span>
            <?php endif; ?>
        </a>

        <?php elseif ($role === 'student'): ?>
        <!-- ── STUDENT MENU ── -->
        <div class="nav-section">Main</div>

        <a href="<?= BASE_URL ?>/dashboards/student.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'student.php') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">My Academic</div>

        <a href="<?= BASE_URL ?>/modules/courses/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/courses/') ? 'active' : '' ?>">
            <i class="bi bi-book"></i> My Courses
        </a>

        <a href="<?= BASE_URL ?>/modules/grades/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/grades/') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i> My Grades
        </a>

        <a href="<?= BASE_URL ?>/modules/attendance/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/attendance/') ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> My Attendance
        </a>

        <div class="nav-section">Communication</div>

        <a href="<?= BASE_URL ?>/modules/announcements/list.php"
           class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/announcements/') ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i> Announcements
            <?php if ($annCount > 0): ?>
            <span class="ms-auto badge bg-warning text-dark"
                  style="font-size:10px;">
                <?= $annCount ?>
            </span>
            <?php endif; ?>
        </a>

        <?php endif; ?>

    </nav>

    <!-- Sidebar footer with user info -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= $avatarLetter ?>
            </div>
            <div class="user-details">
                <h6><?= e(authName()) ?></h6>
                <small><?= e(authRole()) ?></small>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php"
           class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

</aside>
<!-- ══ END SIDEBAR ══════════════════════════════ -->

<!-- ══ MAIN CONTENT AREA ════════════════════════ -->
<main class="sms-main">

    <!-- Top Navbar -->
    <nav class="sms-navbar">
        <div class="navbar-left">
            <!-- Hamburger for mobile -->
            <button class="navbar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="page-title">
                <?= e($pageTitle) ?>
            </span>
        </div>
        <div class="navbar-right">
            <span class="academic-year">
                <i class="bi bi-calendar3 me-1"></i>
                <?= ACADEMIC_YEAR ?>
            </span>
            <div class="nav-user">
                <div class="nav-avatar">
                    <?= $avatarLetter ?>
                </div>
                <span class="nav-user-name d-none d-md-block">
                    <?= e(authName()) ?>
                </span>
            </div>
        </div>
    </nav>

    <!-- Page content starts here -->
    <div class="sms-content">

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="flash-message flash-<?= e($flash['type']) ?>"
         id="flashMessage">
        <i class="bi bi-<?= $flash['type'] === 'success'
            ? 'check-circle'
            : 'exclamation-triangle' ?>"></i>
        <?= e($flash['message']) ?>
        <button onclick="this.parentElement.remove()"
                style="margin-left:auto; background:none;
                       border:none; cursor:pointer;
                       font-size:16px; color:inherit;">
            ✕
        </button>
    </div>
    <?php endif; ?>