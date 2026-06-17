<?php
// ================================================
// File: modules/students/edit.php
// Purpose: Edit existing student record
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireRole('admin');

$pageTitle = 'Edit Student';
$errors    = [];

// Get student ID from URL
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL .
           '/modules/students/list.php');
    exit;
}

// Get classes for dropdown
$classes = $pdo->query("
    SELECT id, name FROM classes
    ORDER BY name
")->fetchAll();

// Fetch existing student data
$stmt = $pdo->prepare("
    SELECT s.*, u.email, u.status as user_status
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

// ── Handle form submission ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    // Check email not taken by another user
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");
        $stmt->execute([$email, $student['user_id']]);
        if ($stmt->fetch())
            $errors[] = 'This email is already
                         used by another account.';
    }

    // Check student number not taken by another
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id FROM students
            WHERE student_no = ?
            AND id != ?
            LIMIT 1
        ");
        $stmt->execute([$student_no, $id]);
        if ($stmt->fetch())
            $errors[] = 'This student number is
                         already used.';
    }

    // ── Save changes ──
    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Update users table
            $stmt = $pdo->prepare("
                UPDATE users
                SET name  = ?,
                    email = ?
                WHERE id  = ?
            ");
            $stmt->execute([
                $first_name . ' ' . $last_name,
                $email,
                $student['user_id']
            ]);

            // Update students table
            $stmt = $pdo->prepare("
                UPDATE students SET
                    first_name     = ?,
                    last_name      = ?,
                    student_no     = ?,
                    dob            = ?,
                    gender         = ?,
                    phone          = ?,
                    address        = ?,
                    guardian_name  = ?,
                    guardian_phone = ?,
                    class_id       = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $first_name, $last_name,
                $student_no,
                $dob     ?: null,
                $gender  ?: null,
                $phone, $address,
                $guardian_name, $guardian_phone,
                $class_id ?: null,
                $id
            ]);

            $pdo->commit();
            setFlash('success',
                'Student updated successfully!');
            header('Location: ' . BASE_URL .
                   '/modules/students/list.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Update failed. Try again.';
        }
    }
}

// Use POST data if errors, else DB data
$d = $_SERVER['REQUEST_METHOD'] === 'POST'
     ? $_POST : $student;

require_once '../../includes/header.php';
?>

<!-- ── Page Header ── -->
<div class="content-header">
    <div class="d-flex justify-content-between
                align-items-center">
        <div>
            <h2>
                <i class="bi bi-pencil me-2
                   text-primary"></i>Edit Student
            </h2>
            <p>Update student record for
               <strong>
                   <?= e($student['first_name']) ?>
                   <?= e($student['last_name'])  ?>
               </strong>
            </p>
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
    <strong>Please fix these errors:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ── Edit Form ── -->
<form method="POST" action="" class="sms-form">

    <div class="row g-4">

        <!-- Personal Details -->
        <div class="col-md-8">
            <div class="sms-card">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-person
                           text-primary"></i>
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
                                value="<?= e($d['first_name']) ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Last Name *
                            </label>
                            <input type="text"
                                class="form-control"
                                name="last_name"
                                value="<?= e($d['last_name']) ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Email Address *
                            </label>
                            <input type="email"
                                class="form-control"
                                name="email"
                                value="<?= e($d['email']) ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Student Number *
                            </label>
                            <input type="text"
                                class="form-control"
                                name="student_no"
                                value="<?= e($d['student_no']) ?>"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Date of Birth
                            </label>
                            <input type="date"
                                class="form-control"
                                name="dob"
                                value="<?= e($d['dob'] ?? '') ?>">
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
                                <?php foreach (
                                    ['Male','Female','Other']
                                    as $g): ?>
                                <option value="<?= $g ?>"
                                    <?= ($d['gender'] ?? '') === $g
                                        ? 'selected' : '' ?>>
                                    <?= $g ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Phone Number
                            </label>
                            <input type="text"
                                class="form-control"
                                name="phone"
                                value="<?= e($d['phone'] ?? '') ?>">
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
                                <?php foreach (
                                    $classes as $class): ?>
                                <option
                                    value="<?= $class['id'] ?>"
                                    <?= ($d['class_id'] ?? '') == $class['id']
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
                            <textarea
                                class="form-control"
                                name="address"
                                rows="2"
                                ><?= e($d['address'] ?? '') ?></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Guardian -->
            <div class="sms-card mt-4">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-people
                           text-primary"></i>
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
                                value="<?= e($d['guardian_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Guardian Phone
                            </label>
                            <input type="text"
                                class="form-control"
                                name="guardian_phone"
                                value="<?= e($d['guardian_phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right sidebar -->
        <div class="col-md-4">

            <!-- Student info card -->
            <div class="sms-card mb-4">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-info-circle
                           text-primary"></i>
                        Student Info
                    </h5>
                </div>
                <div class="card-body">
                    <div style="text-align:center;
                         margin-bottom:16px;">
                        <div style="width:60px;
                             height:60px;
                             background:#e8eaf6;
                             border-radius:50%;
                             display:flex;
                             align-items:center;
                             justify-content:center;
                             font-size:24px;
                             font-weight:700;
                             color:#1a237e;
                             margin:0 auto 10px;">
                            <?= strtoupper(
                                substr($student['first_name'],
                                0, 1)) ?>
                        </div>
                        <div style="font-weight:700;
                             font-size:15px;">
                            <?= e($student['first_name']) ?>
                            <?= e($student['last_name'])  ?>
                        </div>
                        <div style="color:#999;
                             font-size:13px;">
                            <?= e($student['student_no']) ?>
                        </div>
                    </div>
                    <div style="font-size:13px;
                         color:#666;">
                        <div class="mb-2">
                            <strong>Status:</strong>
                            <span class="badge-<?= e($student['status']) ?>
                                  ms-2">
                                <?= ucfirst(e($student['status'])) ?>
                            </span>
                        </div>
                        <div>
                            <strong>Registered:</strong><br>
                            <?= date('d M Y', strtotime(
                                $student['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="sms-card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit"
                                class="btn-primary-sms">
                            <i class="bi bi-check-lg"></i>
                            Save Changes
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

<?php require_once '../../includes/footer.php'; ?>