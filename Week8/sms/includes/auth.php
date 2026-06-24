<?php
// ================================================
// File: C:\xampp\htdocs\sms\includes\auth.php
// Purpose: Session helpers and page protection
// ================================================

// ── Check if user is logged in ──────────────────
// Call this at top of every protected page
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// ── Check if user has a specific role ───────────
// Example: requireRole('admin')
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// ── Check if user has any of multiple roles ─────
// Example: requireAnyRole(['admin','teacher'])
function requireAnyRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['user_role'], $roles)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// ── Get logged in user id ────────────────────────
function authId() {
    return $_SESSION['user_id'] ?? null;
}

// ── Get logged in user role ──────────────────────
function authRole() {
    return $_SESSION['user_role'] ?? null;
}

// ── Get logged in user name ──────────────────────
function authName() {
    return $_SESSION['user_name'] ?? null;
}

// ── Set a flash message ──────────────────────────
// Use to show success/error after redirect
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

// ── Get and clear flash message ──────────────────
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Generate CSRF token ──────────────────────────
// Protects forms from cross-site attacks
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ── Verify CSRF token ────────────────────────────
function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('❌ Invalid request. Please go back and try again.');
    }
}

// ── Safe output helper ───────────────────────────
// Always use e() when outputting user data in HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ── Auto compute letter grade from marks ─────────
function computeGrade($marks) {
    if ($marks >= 90) return 'A+';
    if ($marks >= 80) return 'A';
    if ($marks >= 75) return 'B+';
    if ($marks >= 65) return 'B';
    if ($marks >= 50) return 'C';
    if ($marks >= 40) return 'D';
    return 'F';
}