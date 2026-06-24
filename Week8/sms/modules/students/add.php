<?php
// ================================================
// File: modules/students/add.php
// Purpose: Add a new student
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireRole('admin');

$pageTitle = 'Add Student';
$errors    = [];
$success   = '';

// Get classes for dropdown
$classes = $pdo->query("
    SELECT id, name FROM classes 
    ORDER BY name
")->fetchAll();

// ── Handle form submission ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect inputs
    $first_name     = trim($_POST['first_name']     ?? '');
    $last_name      = trim($_POST['last_name']      ?? '');
    $email          = trim($_POST['email']          ?? '');
    $student_no     = trim($_POST['student_no']     ?? '');
    $phone          = trim($_POST['phone']          ?? '');
    $dob            = trim($_POST['dob']            ?? '');
    $gender         = trim($_POST['gender']         ?? '');
    $class_id       = trim($_POST['class_id']       ?? '');
    $address        = trim($_POST['address']        ?? '');
    $guardian_name  = trim($_POST['guardian_name']  ?? '');
    $guardian_phone = trim($_POST['guardian_phone'] ?? '');
    $password       = trim($_POST['password']       ?? '');

    // Validate
    if (empty($first_name))
        $errors[] = 'First name is required.';
    if (empty($last_name))
        $errors[] = 'Last name is required.';
    if (empty($email))
        $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid email.';
    if (empty($student_no))
        $errors[] = 'Student number is required.';
    if (empty($password))
        $errors[] = 'Password is required.';
    elseif (strlen($password) < 8)
        $errors[] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[A-Z]/', $password))
        $errors[] = 'Password must have an uppercase letter.';
    elseif (!preg_match('/[0-9]/', $password))
        $errors[] = 'Password must have a number.';
    elseif (!preg_match('/[\W_]/', $password))
        $errors[] = 'Password must have a special character.';

    // Check email not already taken
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id FROM users 
            WHERE email = ? LIMIT 1
        ");
        $stmt->execute([$email]);
        if ($stmt->fetch())
            $errors[] = 'This email is already registered.';
    }

    // Check student number not already taken
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id FROM students 
            WHERE student_no = ? LIMIT 1
        ");
        $stmt->execute([$student_no]);
        if ($stmt->fetch())
            $errors[] = 'This student number already exists.';
    }

    // ── Save to database ──
    if (empty($errors)) {
        $hashed = password_hash(
            $password, PASSWORD_BCRYPT, ['cost' => 12]
        );

        $pdo->beginTransaction();
        try {
            // Insert into users
            $stmt = $pdo->prepare("
                INSERT INTO users
                (name, email, password, role, status)
                VALUES (?, ?, ?, 'student', 'active')
            ");
            $stmt->execute([
                $first_name . ' ' . $last_name,
                $email,
                $hashed
            ]);
            $user_id = $pdo->lastInsertId();

            // Insert into students
            $stmt = $pdo->prepare("
                INSERT INTO students
                (user_id, student_no, first_name,
                 last_name, dob, gender, phone,
                 address, guardian_name,
                 guardian_phone, class_id, status)
                VALUES
                (?,?,?,?,?,?,?,?,?,?,?,'active')
            ");
            $stmt->execute([
                $user_id, $student_no,
                $first_name, $last_name,
                $dob ?: null,
                $gender ?: null,
                $phone, $address,
                $guardian_name, $guardian_phone,
                $class_id ?: null
            ]);

            $pdo->commit();
            setFlash('success',
                'Student ' . $first_name . ' ' .
                $last_name . ' added successfully!');
            header('Location: ' . BASE_URL .
                   '/modules/students/list.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to save. Please try again.';
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- ── Page Header ── -->
<div class="content-header">
    <div class="d-flex justify-content-between
                align-items-center">
        <div>
            <h2>
                <i class="bi bi-person-plus me-2
                   text-primary"></i>Add Student
            </h2>
            <p>Create a new student account</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/students/list.php"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Students
        </a>
    </div>
</div>

<!-- ── Error messages ── -->
<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ── Add Student Form ── -->
<form method="POST" action="" class="sms-form">

    <div class="row g-4">

        <!-- Personal Details -->
        <div class="col-md-8">
            <div class="sms-card">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-person text-primary"></i>
                        Personal Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                First Name *
                            </label>
                            <input type="text"
                                class="form-control"
                                name="first_name"
                                value="<?= e($_POST['first_name'] ?? '') ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Last Name *
                            </label>
                            <input type="text"
                                class="form-control"
                                name="last_name"
                                value="<?= e($_POST['last_name'] ?? '') ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Email Address *
                            </label>
                            <input type="email"
                                class="form-control"
                                name="email"
                                value="<?= e($_POST['email'] ?? '') ?>"
                                required>
                            <div id="emailCheck"
                                 style="font-size:12px;
                                        margin-top:4px;">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Student Number *
                            </label>
                            <input type="text"
                                class="form-control"
                                name="student_no"
                                placeholder="e.g. STU-006"
                                value="<?= e($_POST['student_no'] ?? '') ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Date of Birth
                            </label>
                            <input type="date"
                                class="form-control"
                                name="dob"
                                value="<?= e($_POST['dob'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Gender
                            </label>
                            <select class="form-select"
                                    name="gender">
                                <option value="">
                                    Select Gender
                                </option>
                                <option value="Male"
                                    <?= ($_POST['gender'] ?? '') === 'Male'
                                        ? 'selected' : '' ?>>
                                    Male
                                </option>
                                <option value="Female"
                                    <?= ($_POST['gender'] ?? '') === 'Female'
                                        ? 'selected' : '' ?>>
                                    Female
                                </option>
                                <option value="Other"
                                    <?= ($_POST['gender'] ?? '') === 'Other'
                                        ? 'selected' : '' ?>>
                                    Other
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Phone Number
                            </label>
                            <input type="text"
                                class="form-control"
                                name="phone"
                                placeholder="+254 700 000 000"
                                value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Class
                            </label>
                            <select class="form-select"
                                    name="class_id">
                                <option value="">
                                    Select Class
                                </option>
                                <?php foreach ($classes as $class): ?>
                                <option
                                    value="<?= $class['id'] ?>"
                                    <?= ($_POST['class_id'] ?? '') == $class['id']
                                        ? 'selected' : '' ?>>
                                    <?= e($class['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Address
                            </label>
                            <textarea class="form-control"
                                name="address"
                                rows="2"
                                placeholder="Home address"
                                ><?= e($_POST['address'] ?? '') ?></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Guardian Details -->
            <div class="sms-card mt-4">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-people text-primary"></i>
                        Guardian Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Guardian Name
                            </label>
                            <input type="text"
                                class="form-control"
                                name="guardian_name"
                                value="<?= e($_POST['guardian_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Guardian Phone
                            </label>
                            <input type="text"
                                class="form-control"
                                name="guardian_phone"
                                value="<?= e($_POST['guardian_phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Details sidebar -->
        <div class="col-md-4">
            <div class="sms-card">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-lock text-primary"></i>
                        Account Details
                    </h5>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Password *
                        </label>
                        <input type="password"
                            class="form-control"
                            name="password"
                            id="password"
                            placeholder="Strong password"
                            required>

                        <!-- Strength bar -->
                        <div style="height:5px;
                             background:#eee;
                             border-radius:3px;
                             margin-top:8px;
                             overflow:hidden;">
                            <div id="strengthBar"
                                 style="height:100%;
                                        width:0%;
                                        border-radius:3px;
                                        transition:all 0.3s;">
                            </div>
                        </div>
                        <div id="strengthText"
                             style="font-size:11px;
                                    margin-top:3px;">
                        </div>

                        <!-- Criteria -->
                        <ul style="list-style:none;
                             padding:0;margin:8px 0 0;
                             font-size:11px;">
                            <li id="c1" style="color:#999;">
                                <i class="bi bi-x-circle"></i>
                                8+ characters
                            </li>
                            <li id="c2" style="color:#999;">
                                <i class="bi bi-x-circle"></i>
                                Uppercase letter
                            </li>
                            <li id="c3" style="color:#999;">
                                <i class="bi bi-x-circle"></i>
                                Number
                            </li>
                            <li id="c4" style="color:#999;">
                                <i class="bi bi-x-circle"></i>
                                Special character
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-info
                                p-2 mt-3"
                         style="font-size:12px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Student will use this password
                        to log in to the system.
                    </div>

                </div>
            </div>

            <!-- Submit button -->
            <div class="sms-card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit"
                                class="btn-primary-sms">
                            <i class="bi bi-person-plus"></i>
                            Save Student
                        </button>
                        <a href="<?= BASE_URL ?>/modules/students/list.php"
                           class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<?php
$extraScripts = "
<script>
// Password strength checker
const pwd = document.getElementById('password');
pwd.addEventListener('input', function() {
    const v = pwd.value;
    const c1 = v.length >= 8;
    const c2 = /[A-Z]/.test(v);
    const c3 = /[0-9]/.test(v);
    const c4 = /[\W_]/.test(v);
    const score = [c1,c2,c3,c4].filter(Boolean).length;

    updateC('c1', c1);
    updateC('c2', c2);
    updateC('c3', c3);
    updateC('c4', c4);

    const colors = ['','#c62828','#e65100',
                    '#f9a825','#2e7d32'];
    const labels = ['','Weak','Fair',
                    'Good','Strong 💪'];
    const bar = document.getElementById('strengthBar');
    bar.style.width = (score * 25) + '%';
    bar.style.background = colors[score];
    const txt = document.getElementById('strengthText');
    txt.textContent = labels[score];
    txt.style.color = colors[score];
});

function updateC(id, met) {
    const el = document.getElementById(id);
    const ic = el.querySelector('i');
    if (met) {
        el.style.color = '#2e7d32';
        ic.className = 'bi bi-check-circle-fill';
    } else {
        el.style.color = '#999';
        ic.className = 'bi bi-x-circle';
    }
}
</script>
";
require_once '../../includes/footer.php';
?>