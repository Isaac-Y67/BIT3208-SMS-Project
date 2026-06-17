<?php
// ================================================
// File: modules/attendance/list.php
// Purpose: Mark and view attendance
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireAnyRole(['admin', 'teacher']);

$pageTitle = 'Attendance';

// Get courses based on role
if (authRole() === 'admin') {
    $courses = $pdo->query("
        SELECT c.*, t.first_name, t.last_name
        FROM courses c
        LEFT JOIN teachers t ON t.id = c.teacher_id
        WHERE c.status = 'active'
        ORDER BY c.name
    ")->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, t.first_name, t.last_name
        FROM courses c
        LEFT JOIN teachers t ON t.id = c.teacher_id
        WHERE c.status = 'active'
        AND t.user_id = ?
        ORDER BY c.name
    ");
    $stmt->execute([authId()]);
    $courses = $stmt->fetchAll();
}

// Selected course and date
$courseId = intval($_GET['course_id'] ?? 0);
$date     = $_GET['date'] ?? date('Y-m-d');

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postCourseId  = intval($_POST['course_id'] ?? 0);
    $postDate      = $_POST['date'] ?? date('Y-m-d');
    $attendanceArr = $_POST['attendance'] ?? [];

    $saved = 0;
    foreach ($attendanceArr as $studentId => $status) {
        $allowed = ['Present','Absent','Late','Excused'];
        if (!in_array($status, $allowed)) continue;

        // Insert or update attendance
        $stmt = $pdo->prepare("
            INSERT INTO attendance
            (student_id, course_id, date, status)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                status = VALUES(status)
        ");
        $stmt->execute([
            $studentId, $postCourseId,
            $postDate, $status
        ]);
        $saved++;
    }

    setFlash('success',
        'Attendance saved for ' . $saved .
        ' student(s) on ' .
        date('d M Y', strtotime($postDate)) . '!');
    header('Location: ' . BASE_URL .
           '/modules/attendance/list.php?course_id=' .
           $postCourseId . '&date=' . $postDate);
    exit;
}

// Get enrolled students with their attendance
$students = [];
if ($courseId) {
    $stmt = $pdo->prepare("
        SELECT s.id, s.first_name, s.last_name,
               s.student_no,
               a.status as attendance_status
        FROM enrollments e
        JOIN students s ON s.id = e.student_id
        LEFT JOIN attendance a
            ON a.student_id = e.student_id
            AND a.course_id = ?
            AND a.date      = ?
        WHERE e.course_id = ?
        ORDER BY s.first_name
    ");
    $stmt->execute([$courseId, $date, $courseId]);
    $students = $stmt->fetchAll();
}

// Attendance summary for selected course
$summary = [];
if ($courseId) {
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as total
        FROM attendance
        WHERE course_id = ?
        GROUP BY status
    ");
    $stmt->execute([$courseId]);
    foreach ($stmt->fetchAll() as $row) {
        $summary[$row['status']] = $row['total'];
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
                <i class="bi bi-calendar-check me-2
                   text-primary"></i>Attendance
            </h2>
            <p>Mark and manage daily attendance</p>
        </div>
    </div>
</div>

<!-- ── Course + Date Selector ── -->
<div class="sms-card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        Select Course
                    </label>
                    <select class="form-select"
                            name="course_id">
                        <option value="">
                            — Choose a course —
                        </option>
                        <?php foreach (
                            $courses as $course): ?>
                        <option
                            value="<?= $course['id'] ?>"
                            <?= $courseId == $course['id']
                                ? 'selected' : '' ?>>
                            <?= e($course['code']) ?> —
                            <?= e($course['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        Date
                    </label>
                    <input type="date"
                        class="form-control"
                        name="date"
                        value="<?= e($date) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit"
                            class="btn-primary-sms
                                   w-100">
                        <i class="bi bi-search"></i>
                        Load
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Summary Cards ── -->
<?php if ($courseId && !empty($summary)): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $summary['Present'] ?? 0 ?></h3>
                <p>Present</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $summary['Absent'] ?? 0 ?></h3>
                <p>Absent</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?= $summary['Late'] ?? 0 ?></h3>
                <p>Late</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="stat-info">
                <h3><?= $summary['Excused'] ?? 0 ?></h3>
                <p>Excused</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Attendance Table ── -->
<?php if ($courseId && !empty($students)): ?>
<div class="sms-card">
    <div class="card-header">
        <h5>
            <i class="bi bi-calendar-check
               text-primary"></i>
            Mark Attendance —
            <?= date('d M Y', strtotime($date)) ?>
        </h5>
        <span class="badge bg-primary">
            <?= count($students) ?> students
        </span>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="">
            <input type="hidden"
                   name="course_id"
                   value="<?= $courseId ?>">
            <input type="hidden"
                   name="date"
                   value="<?= e($date) ?>">

            <table class="sms-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Student No</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Excused</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (
                        $students as $i => $s):
                        $current = $s['attendance_status']
                                ?? 'Present';
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong>
                                <?= e($s['first_name']) ?>
                                <?= e($s['last_name'])  ?>
                            </strong>
                        </td>
                        <td>
                            <span class="badge-admin">
                                <?= e($s['student_no']) ?>
                            </span>
                        </td>
                        <?php foreach (
                            ['Present','Absent',
                             'Late','Excused']
                            as $status): ?>
                        <td style="text-align:center;">
                            <input
                                type="radio"
                                name="attendance[<?= $s['id'] ?>]"
                                value="<?= $status ?>"
                                <?= $current === $status
                                    ? 'checked' : '' ?>
                                style="width:18px;
                                       height:18px;
                                       cursor:pointer;">
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Mark all buttons -->
            <div style="padding:12px 20px;
                 background:#f8f9ff;
                 border-top:1px solid #f0f0f0;
                 display:flex;gap:10px;
                 flex-wrap:wrap;">
                <span style="font-size:13px;
                      font-weight:600;
                      color:#555;
                      align-self:center;">
                    Mark all as:
                </span>
                <?php foreach (
                    ['Present','Absent',
                     'Late','Excused']
                    as $status): ?>
                <button type="button"
                        onclick="markAll('<?= $status ?>')"
                        class="btn btn-sm
                               btn-outline-secondary">
                    <?= $status ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Save button -->
            <div style="padding:16px 20px;
                 border-top:1px solid #f0f0f0;">
                <button type="submit"
                        class="btn-primary-sms">
                    <i class="bi bi-check-lg"></i>
                    Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($courseId && empty($students)): ?>
<div class="sms-card">
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <h5>No students enrolled</h5>
        <p>No students found for this course</p>
    </div>
</div>

<?php else: ?>
<div class="sms-card">
    <div class="empty-state">
        <i class="bi bi-calendar-check"></i>
        <h5>Select a course to begin</h5>
        <p>Choose a course and date above
           to mark attendance</p>
    </div>
</div>
<?php endif; ?>

<?php
$extraScripts = "
<script>
// Mark all students with same status
function markAll(status) {
    const radios = document.querySelectorAll(
        'input[type=radio][value=' + status + ']'
    );
    radios.forEach(r => r.checked = true);
}
</script>
";
require_once '../../includes/footer.php';
?>