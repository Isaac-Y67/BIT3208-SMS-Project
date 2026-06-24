<?php
// ================================================
// File: modules/grades/list.php
// Purpose: View and enter grades
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireAnyRole(['admin', 'teacher']);

$pageTitle = 'Grades';

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
    // Teacher sees only their courses
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

// Selected course and exam type
$courseId = intval($_GET['course_id'] ?? 0);
$examType = $_GET['exam_type'] ?? 'CA';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postCourseId = intval($_POST['course_id'] ?? 0);
    $postExamType = $_POST['exam_type'] ?? 'CA';
    $grades       = $_POST['grades'] ?? [];

    $saved = 0;
    foreach ($grades as $studentId => $marks) {
        $marks = floatval($marks);
        if ($marks < 0)   $marks = 0;
        if ($marks > 100) $marks = 100;

        $grade = computeGrade($marks);

        // Insert or update grade
        $stmt = $pdo->prepare("
            INSERT INTO grades
            (student_id, course_id, exam_type,
             marks, grade, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                marks       = VALUES(marks),
                grade       = VALUES(grade),
                recorded_by = VALUES(recorded_by),
                recorded_at = NOW()
        ");
        $stmt->execute([
            $studentId, $postCourseId,
            $postExamType, $marks, $grade,
            authId()
        ]);
        $saved++;
    }

    setFlash('success',
        $saved . ' grade(s) saved successfully!');
    header('Location: ' . BASE_URL .
           '/modules/grades/list.php?course_id=' .
           $postCourseId .
           '&exam_type=' . $postExamType);
    exit;
}

// Get enrolled students for selected course
$students = [];
if ($courseId) {
    $stmt = $pdo->prepare("
        SELECT s.id, s.first_name, s.last_name,
               s.student_no,
               g.marks, g.grade, g.remarks
        FROM enrollments e
        JOIN students s ON s.id = e.student_id
        LEFT JOIN grades g
            ON g.student_id = e.student_id
            AND g.course_id = ?
            AND g.exam_type = ?
        WHERE e.course_id = ?
        ORDER BY s.first_name
    ");
    $stmt->execute([$courseId, $examType, $courseId]);
    $students = $stmt->fetchAll();
}

require_once '../../includes/header.php';
?>

<!-- ── Page Header ── -->
<div class="content-header">
    <div class="d-flex justify-content-between
                align-items-center">
        <div>
            <h2>
                <i class="bi bi-bar-chart me-2
                   text-primary"></i>Grades
            </h2>
            <p>Enter and manage student grades</p>
        </div>
    </div>
</div>

<!-- ── Course + Exam Type Selector ── -->
<div class="sms-card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        Select Course
                    </label>
                    <select class="form-select"
                            name="course_id"
                            required>
                        <option value="">
                            — Choose a course —
                        </option>
                        <?php foreach ($courses
                                       as $course): ?>
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
                        Exam Type
                    </label>
                    <select class="form-select"
                            name="exam_type">
                        <?php foreach (
                            ['CA','Midterm','Final']
                            as $type): ?>
                        <option value="<?= $type ?>"
                            <?= $examType === $type
                                ? 'selected' : '' ?>>
                            <?= $type ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit"
                            class="btn-primary-sms
                                   w-100">
                        <i class="bi bi-search"></i>
                        Load Students
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Grading Scale Reference ── -->
<div class="sms-card mb-4">
    <div class="card-body py-2">
        <div class="d-flex gap-3 flex-wrap
                    align-items-center">
            <strong style="font-size:13px;">
                Grading Scale:
            </strong>
            <?php
            $scale = [
                'A+'=>'≥90','A'=>'≥80','B+'=>'≥75',
                'B'=>'≥65','C'=>'≥50','D'=>'≥40',
                'F'=>'<40'
            ];
            foreach ($scale as $grade => $range): ?>
            <span class="badge-<?=
                $grade === 'F' ? 'inactive' : 'active'
                ?>" style="font-size:12px;">
                <?= $grade ?>: <?= $range ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Grades Table ── -->
<?php if ($courseId && !empty($students)): ?>
<div class="sms-card">
    <div class="card-header">
        <h5>
            <i class="bi bi-pencil-square
               text-primary"></i>
            Enter Marks —
            <?= e($examType) ?> Exam
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
                   name="exam_type"
                   value="<?= e($examType) ?>">

            <table class="sms-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Student No</th>
                        <th>Marks (0-100)</th>
                        <th>Grade</th>
                        <th>Current Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (
                        $students as $i => $s): ?>
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
                        <td>
                            <input
                                type="number"
                                name="grades[<?= $s['id'] ?>]"
                                class="form-control
                                       grade-input"
                                style="width:100px;"
                                min="0" max="100"
                                step="0.5"
                                value="<?= e($s['marks'] ?? '') ?>"
                                placeholder="0-100"
                                oninput="updateGrade(
                                    this,
                                    'grade_<?= $s['id'] ?>'
                                )">
                        </td>
                        <td>
                            <span id="grade_<?= $s['id'] ?>"
                                  class="badge-<?=
                                      !empty($s['marks'])
                                      ? ($s['grade'] === 'F'
                                         ? 'inactive'
                                         : 'active')
                                      : 'student' ?>">
                                <?= !empty($s['marks'])
                                    ? e($s['grade'])
                                    : '—' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($s['marks']): ?>
                            <span style="font-size:13px;
                                  color:#666;">
                                <?= e($s['marks']) ?>/100
                            </span>
                            <?php else: ?>
                            <span style="color:#ccc;
                                  font-size:12px;">
                                Not recorded
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Save button -->
            <div style="padding:16px 20px;
                 border-top:1px solid #f0f0f0;">
                <button type="submit"
                        class="btn-primary-sms">
                    <i class="bi bi-check-lg"></i>
                    Save All Grades
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
        <p>No students are enrolled in
           this course yet</p>
    </div>
</div>

<?php else: ?>
<div class="sms-card">
    <div class="empty-state">
        <i class="bi bi-bar-chart"></i>
        <h5>Select a course to begin</h5>
        <p>Choose a course and exam type
           above to load students</p>
    </div>
</div>
<?php endif; ?>

<?php
$extraScripts = "
<script>
// Auto compute grade as teacher types marks
function updateGrade(input, gradeId) {
    const marks = parseFloat(input.value);
    const el    = document.getElementById(gradeId);
    if (isNaN(marks) || input.value === '') {
        el.textContent = '—';
        el.className   = 'badge-student';
        return;
    }
    let grade = 'F';
    if      (marks >= 90) grade = 'A+';
    else if (marks >= 80) grade = 'A';
    else if (marks >= 75) grade = 'B+';
    else if (marks >= 65) grade = 'B';
    else if (marks >= 50) grade = 'C';
    else if (marks >= 40) grade = 'D';

    el.textContent = grade;
    el.className   = grade === 'F'
        ? 'badge-inactive'
        : 'badge-active';
}
</script>
";
require_once '../../includes/footer.php';
?>