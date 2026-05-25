<?php
// ================================================
// File: C:\xampp\htdocs\sms\index.php
// Purpose: Login page for all three roles
// ================================================
require_once 'includes/config.php';
require_once 'includes/db.php';

$error   = '';
$success = '';

// ── If already logged in redirect to dashboard ──
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Handle login form submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } else {
        // Check user in database
        $stmt = $pdo->prepare(
            "SELECT * FROM users 
             WHERE email = ? AND status = 'active' 
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // ✅ Login successful
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email']= $user['email'];

            // Redirect based on role
            header('Location: dashboard.php');
            exit;

        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ── Page background ── */
        body {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #1565c0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        /* ── Login card ── */
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }

        /* ── Logo area ── */
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1a237e, #e65100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        .logo-icon i {
            font-size: 32px;
            color: white;
        }
        .logo-area h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a237e;
            margin: 0;
        }
        .logo-area p {
            color: #888;
            font-size: 13px;
            margin: 4px 0 0;
        }

        /* ── Form inputs ── */
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            padding: 12px 16px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 3px rgba(26,35,126,0.1);
        }
        .input-group-text {
            background: #f5f5f5;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px 0 0 8px;
            color: #1a237e;
        }

        /* ── Login button ── */
        .btn-login {
            background: linear-gradient(135deg, #1a237e, #283593);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            padding: 12px;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #283593, #1a237e);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(26,35,126,0.4);
            color: white;
        }

        /* ── Error alert ── */
        .alert {
            border-radius: 8px;
            font-size: 14px;
        }

        /* ── Register link ── */
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .register-link a {
            color: #1a237e;
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }

        /* ── Password toggle ── */
        .toggle-password {
            cursor: pointer;
            border: 1.5px solid #e0e0e0;
            background: #f5f5f5;
            border-radius: 0 8px 8px 0;
        }
        .toggle-password:hover {
            background: #e0e0e0;
        }

        /* ── Validation feedback ── */
        .invalid-feedback {
            font-size: 12px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- Logo -->
    <div class="logo-area">
        <div class="logo-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h1><?= APP_SHORT ?></h1>
        <p><?= APP_NAME ?></p>
        <small class="text-muted"><?= ACADEMIC_YEAR ?></small>
    </div>

    <!-- Error message -->
    <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Login form -->
    <form id="loginForm" method="POST" action="" novalidate>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Email Address
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    autofocus>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-1"></i>Password
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                </span>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required>
                <button
                    class="btn toggle-password"
                    type="button"
                    id="togglePassword"
                    tabindex="-1">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
                <div class="invalid-feedback">
                    Please enter your password.
                </div>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-login" id="loginBtn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>

    </form>

    <!-- Register link -->
    <div class="register-link">
        Don't have an account?
        <a href="register.php">Register here</a>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================================================
// JavaScript — Form Validation + UI Interactions
// Week 3 Task 1: JavaScript Basics
// Week 3 Task 2: DOM Manipulation
// ================================================

// ── 1. Password show/hide toggle ──
const togglePassword = document.getElementById('togglePassword');
const passwordInput  = document.getElementById('password');
const eyeIcon        = document.getElementById('eyeIcon');

togglePassword.addEventListener('click', function() {
    // Toggle between text and password type
    const type = passwordInput.getAttribute('type') === 'password'
        ? 'text'
        : 'password';
    passwordInput.setAttribute('type', type);

    // Swap eye icon
    eyeIcon.classList.toggle('bi-eye');
    eyeIcon.classList.toggle('bi-eye-slash');
});

// ── 2. Client-side form validation ──
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');

loginForm.addEventListener('submit', function(e) {

    let valid = true;

    // Check email
    const emailVal = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailVal) {
        showError(emailInput, 'Email address is required.');
        valid = false;
    } else if (!emailRegex.test(emailVal)) {
        showError(emailInput, 'Please enter a valid email address.');
        valid = false;
    } else {
        showSuccess(emailInput);
    }

    // Check password
    const passVal = passwordInput.value.trim();
    if (!passVal) {
        showError(passwordInput, 'Password is required.');
        valid = false;
    } else {
        showSuccess(passwordInput);
    }

    // Stop form if invalid
    if (!valid) {
        e.preventDefault();
        e.stopPropagation();
    }
});

// ── 3. Real-time email validation as user types ──
emailInput.addEventListener('input', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (emailInput.value && !emailRegex.test(emailInput.value)) {
        showError(emailInput, 'Please enter a valid email address.');
    } else if (emailInput.value) {
        showSuccess(emailInput);
    }
});

// ── Helper: show error on input ──
function showError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    const feedback = input.parentElement.querySelector('.invalid-feedback');
    if (feedback) feedback.textContent = message;
}

// ── Helper: show success on input ──
function showSuccess(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}
</script>

</body>
</html>