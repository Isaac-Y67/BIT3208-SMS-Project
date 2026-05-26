<?php
// ================================================
// File: C:\xampp\htdocs\sms\register.php
// Purpose: Student self-registration page
// ================================================
require_once 'includes/config.php';
require_once 'includes/db.php';

$error   = '';
$success = '';

// ── If already logged in redirect ──
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Handle registration form submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect inputs
    $full_name   = trim($_POST['full_name']   ?? '');
    $email       = trim($_POST['email']       ?? '');
    $student_no  = trim($_POST['student_no']  ?? '');
    $phone       = trim($_POST['phone']       ?? '');
    $password    = trim($_POST['password']    ?? '');
    $confirm     = trim($_POST['confirm']     ?? '');

    // ── Server-side validation ──
    if (empty($full_name) || empty($email) || empty($student_no) 
        || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';

    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';

    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';

    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';

    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';

    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = 'Password must contain at least one special character (!@#$%).';

    } else {
        // Check if email already exists
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email address is already registered. 
                      Please use a different email or login.';
        } else {
            // Check if student number already exists
            $stmt2 = $pdo->prepare(
                "SELECT id FROM students WHERE student_no = ? LIMIT 1"
            );
            $stmt2->execute([$student_no]);
            if ($stmt2->fetch()) {
                $error = 'This student number is already registered.';
            } else {
                // ✅ All good — create the account
                // Hash password with bcrypt
                $hashed = password_hash($password, PASSWORD_BCRYPT, 
                                        ['cost' => 12]);

                // Split full name into first and last
                $parts      = explode(' ', $full_name, 2);
                $first_name = $parts[0];
                $last_name  = $parts[1] ?? '';

                // Begin transaction — both inserts must succeed
                $pdo->beginTransaction();
                try {
                    // Insert into users table
                    $stmt3 = $pdo->prepare(
                        "INSERT INTO users 
                         (name, email, password, role, status) 
                         VALUES (?, ?, ?, 'student', 'inactive')"
                    );
                    $stmt3->execute([$full_name, $email, $hashed]);
                    $user_id = $pdo->lastInsertId();

                    // Insert into students table
                    $stmt4 = $pdo->prepare(
                        "INSERT INTO students 
                         (user_id, student_no, first_name, 
                          last_name, phone, status) 
                         VALUES (?, ?, ?, ?, ?, 'active')"
                    );
                    $stmt4->execute([
                        $user_id, $student_no, 
                        $first_name, $last_name, $phone
                    ]);

                    $pdo->commit();
                    $success = 'Registration successful! Your account is 
                                pending admin approval. You will be able 
                                to login once activated.';

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #1565c0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px 0;
        }
        .register-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        .logo-area {
            text-align: center;
            margin-bottom: 25px;
        }
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1a237e, #e65100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        .logo-icon i { font-size: 28px; color: white; }
        .logo-area h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1a237e;
            margin: 0;
        }
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            padding: 10px 16px;
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
            color: #1a237e;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }
        .btn-register {
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
        .btn-register:hover {
            background: linear-gradient(135deg, #283593, #1a237e);
            transform: translateY(-1px);
            color: white;
        }
        .toggle-password {
            cursor: pointer;
            border: 1.5px solid #e0e0e0;
            background: #f5f5f5;
        }

        /* ── Password strength bar ── */
        .strength-bar-wrap {
            height: 6px;
            background: #eee;
            border-radius: 3px;
            margin-top: 8px;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.4s, background 0.4s;
        }
        .strength-text {
            font-size: 12px;
            font-weight: 600;
            margin-top: 4px;
        }

        /* ── Password criteria checklist ── */
        .criteria-list {
            list-style: none;
            padding: 0;
            margin: 8px 0 0;
            font-size: 12px;
        }
        .criteria-list li {
            padding: 2px 0;
            color: #999;
            transition: color 0.3s;
        }
        .criteria-list li.met {
            color: #2e7d32;
        }
        .criteria-list li i {
            margin-right: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-link a {
            color: #1a237e;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="register-card">

    <!-- Logo -->
    <div class="logo-area">
        <div class="logo-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h1>Student Registration</h1>
        <p class="text-muted" style="font-size:13px;">
            <?= APP_NAME ?> — <?= ACADEMIC_YEAR ?>
        </p>
    </div>

    <!-- Success message -->
    <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
        <div class="login-link">
            <a href="index.php">← Back to Login</a>
        </div>

    <?php else: ?>

    <!-- Error message -->
    <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Registration form -->
    <form id="registerForm" method="POST" action="" novalidate>

        <!-- Full Name -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-person me-1"></i>Full Name
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text"
                    class="form-control"
                    name="full_name"
                    id="full_name"
                    placeholder="e.g. Alice Njeri"
                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                    required>
                <div class="invalid-feedback">
                    Full name is required.
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-envelope me-1"></i>Email Address
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email"
                    class="form-control"
                    name="email"
                    id="email"
                    placeholder="student@school.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>
            <!-- Live email check feedback -->
            <div id="emailFeedback" class="mt-1" style="font-size:12px;"></div>
        </div>

        <!-- Student Number -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-hash me-1"></i>Student Number
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-hash"></i>
                </span>
                <input type="text"
                    class="form-control"
                    name="student_no"
                    id="student_no"
                    placeholder="e.g. STU-006"
                    value="<?= htmlspecialchars($_POST['student_no'] ?? '') ?>"
                    required>
                <div class="invalid-feedback">
                    Student number is required.
                </div>
            </div>
        </div>

        <!-- Phone -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-phone me-1"></i>Phone Number
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-phone"></i>
                </span>
                <input type="text"
                    class="form-control"
                    name="phone"
                    id="phone"
                    placeholder="+254 700 000 000"
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-lock me-1"></i>Password
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password"
                    class="form-control"
                    name="password"
                    id="password"
                    placeholder="Create a strong password"
                    required>
                <button class="btn toggle-password"
                    type="button"
                    id="togglePass">
                    <i class="bi bi-eye" id="eyePass"></i>
                </button>
                <div class="invalid-feedback">
                    Password is required.
                </div>
            </div>

            <!-- Strength bar -->
            <div class="strength-bar-wrap">
                <div class="strength-bar" id="strengthBar"></div>
            </div>
            <div class="strength-text" id="strengthText"></div>

            <!-- Criteria checklist -->
            <ul class="criteria-list" id="criteriaList">
                <li id="c-length">
                    <i class="bi bi-x-circle"></i>At least 8 characters
                </li>
                <li id="c-upper">
                    <i class="bi bi-x-circle"></i>At least one uppercase letter (A-Z)
                </li>
                <li id="c-lower">
                    <i class="bi bi-x-circle"></i>At least one lowercase letter (a-z)
                </li>
                <li id="c-number">
                    <i class="bi bi-x-circle"></i>At least one number (0-9)
                </li>
                <li id="c-special">
                    <i class="bi bi-x-circle"></i>At least one special character (!@#$%)
                </li>
            </ul>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label">
                <i class="bi bi-lock-fill me-1"></i>Confirm Password
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <input type="password"
                    class="form-control"
                    name="confirm"
                    id="confirm"
                    placeholder="Repeat your password"
                    required>
                <button class="btn toggle-password"
                    type="button"
                    id="toggleConfirm">
                    <i class="bi bi-eye" id="eyeConfirm"></i>
                </button>
                <div class="invalid-feedback">
                    Passwords do not match.
                </div>
            </div>
            <div id="matchFeedback"
                 style="font-size:12px; margin-top:4px;">
            </div>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="btn btn-register"
                id="registerBtn">
            <i class="bi bi-person-plus me-2"></i>Create Account
        </button>

    </form>

    <div class="login-link">
        Already have an account?
        <a href="index.php">Login here</a>
    </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================================================
// JavaScript — Password Strength Checker
// Week 3 Task 1 + Task 2
// ================================================

const passwordInput  = document.getElementById('password');
const confirmInput   = document.getElementById('confirm');
const strengthBar    = document.getElementById('strengthBar');
const strengthText   = document.getElementById('strengthText');

// ── Criteria elements ──
const cLength  = document.getElementById('c-length');
const cUpper   = document.getElementById('c-upper');
const cLower   = document.getElementById('c-lower');
const cNumber  = document.getElementById('c-number');
const cSpecial = document.getElementById('c-special');

// ── Password show/hide toggles ──
document.getElementById('togglePass').addEventListener('click', function() {
    toggleVisibility(passwordInput, document.getElementById('eyePass'));
});
document.getElementById('toggleConfirm').addEventListener('click', function() {
    toggleVisibility(confirmInput, document.getElementById('eyeConfirm'));
});
function toggleVisibility(input, icon) {
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
}

// ── Real-time password strength checker ──
passwordInput.addEventListener('input', function() {
    const val = passwordInput.value;

    // Check each criteria
    const hasLength  = val.length >= 8;
    const hasUpper   = /[A-Z]/.test(val);
    const hasLower   = /[a-z]/.test(val);
    const hasNumber  = /[0-9]/.test(val);
    const hasSpecial = /[\W_]/.test(val);

    // Update criteria list
    updateCriteria(cLength,  hasLength);
    updateCriteria(cUpper,   hasUpper);
    updateCriteria(cLower,   hasLower);
    updateCriteria(cNumber,  hasNumber);
    updateCriteria(cSpecial, hasSpecial);

    // Calculate strength score 0-5
    const score = [hasLength, hasUpper, hasLower,
                   hasNumber, hasSpecial]
                  .filter(Boolean).length;

    // Update strength bar
    const levels = [
        { width: '0%',   color: '#eee',    label: '',          },
        { width: '20%',  color: '#c62828', label: '🔴 Very Weak'  },
        { width: '40%',  color: '#e65100', label: '🟠 Weak'       },
        { width: '60%',  color: '#f9a825', label: '🟡 Fair'       },
        { width: '80%',  color: '#2e7d32', label: '🟢 Strong'     },
        { width: '100%', color: '#1a237e', label: '💪 Very Strong' },
    ];

    const level = levels[score];
    strengthBar.style.width      = level.width;
    strengthBar.style.background = level.color;
    strengthText.textContent     = level.label;
    strengthText.style.color     = level.color;

    // Also check confirm match
    checkMatch();
});

// ── Update single criteria item ──
function updateCriteria(element, met) {
    const icon = element.querySelector('i');
    if (met) {
        element.classList.add('met');
        icon.className = 'bi bi-check-circle-fill';
    } else {
        element.classList.remove('met');
        icon.className = 'bi bi-x-circle';
    }
}

// ── Real-time confirm password match ──
confirmInput.addEventListener('input', checkMatch);
function checkMatch() {
    const feedback = document.getElementById('matchFeedback');
    if (!confirmInput.value) {
        feedback.textContent = '';
        return;
    }
    if (passwordInput.value === confirmInput.value) {
        feedback.innerHTML =
            '<span style="color:#2e7d32;">✅ Passwords match</span>';
        confirmInput.classList.remove('is-invalid');
        confirmInput.classList.add('is-valid');
    } else {
        feedback.innerHTML =
            '<span style="color:#c62828;">❌ Passwords do not match</span>';
        confirmInput.classList.add('is-invalid');
        confirmInput.classList.remove('is-valid');
    }
}

// ── Real-time email validation ──
const emailInput    = document.getElementById('email');
const emailFeedback = document.getElementById('emailFeedback');
emailInput.addEventListener('input', function() {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailInput.value) {
        emailFeedback.textContent = '';
    } else if (!regex.test(emailInput.value)) {
        emailFeedback.innerHTML =
            '<span style="color:#c62828;">❌ Invalid email format</span>';
        emailInput.classList.add('is-invalid');
        emailInput.classList.remove('is-valid');
    } else {
        emailFeedback.innerHTML =
            '<span style="color:#2e7d32;">✅ Valid email</span>';
        emailInput.classList.remove('is-invalid');
        emailInput.classList.add('is-valid');
    }
});

// ── Form submission validation ──
document.getElementById('registerForm')
        .addEventListener('submit', function(e) {

    let valid = true;

    // Check all required fields
    ['full_name','email','student_no','password','confirm']
    .forEach(function(id) {
        const field = document.getElementById(id);
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            valid = false;
        }
    });

    // Check password strength — must be score 3+
    const val     = passwordInput.value;
    const score   = [
        val.length >= 8,
        /[A-Z]/.test(val),
        /[a-z]/.test(val),
        /[0-9]/.test(val),
        /[\W_]/.test(val)
    ].filter(Boolean).length;

    if (score < 5) {
        passwordInput.classList.add('is-invalid');
        valid = false;
    }

    // Check passwords match
    if (passwordInput.value !== confirmInput.value) {
        confirmInput.classList.add('is-invalid');
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
    }
});
</script>

</body>
</html>