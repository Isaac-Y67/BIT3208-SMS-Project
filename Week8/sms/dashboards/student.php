<?php
// ================================================
// File: dashboards/student.php
// Purpose: Student dashboard
// ================================================
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('student');

$pageTitle = 'Student Dashboard';

// Get student profile
$stmt = $pdo->prepare("
    SELECT s.*, u.email, c.name as class_name
    FROM students s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN classes c ON c.id = s.class_id
    WHERE s.user_id = ?
");
$stmt->execute([authId()]);
$student   = $stmt->fetch();
$studentId = $student['id'] ?? 0;

// Total enrolled courses
$totalCourses = 0;
if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM enrollments
        WHERE student_id = ?
    ");
    $stmt->execute([$studentId]);
    $totalCourses = $stmt->fetchColumn();
}

// Overall average marks
$gpa = 0;
if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT ROUND(AVG(marks), 1) as gpa
        FROM grades
        WHERE student_id = ?
    ");
    $stmt->execute([$studentId]);
    $gpa = $stmt->fetchColumn() ?? 0;
}

// Overall attendance percentage
$attPct = 0;
if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Present'
                     OR status = 'Late'
                     THEN 1 ELSE 0 END) as present
        FROM attendance
        WHERE student_id = ?
    ");
    $stmt->execute([$studentId]);
    $attRow = $stmt->fetch();
    $attPct = (!empty($attRow) && $attRow['total'] > 0)
        ? round(($attRow['present'] / $attRow['total']) * 100)
        : 0;
}

// Unread announcements
$stmt = $pdo->query("
    SELECT COUNT(*) FROM announcements
    WHERE is_active = 1
    AND (target_role = 'all'
         OR target_role = 'student')
");
$totalAnnouncements = $stmt->fetchColumn();

// My enrolled courses with grades
$myCourses = [];
if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT c.id, c.code, c.name,
               t.first_name, t.last_name,
               cl.name as class_name,
               ROUND(AVG(g.marks),1) as avg_marks,
               COUNT(g.id) as grade_count
        FROM enrollments e
        JOIN courses c ON c.id = e.course_id
        LEFT JOIN teachers t ON t.id = c.teacher_id
        LEFT JOIN classes cl ON cl.id = c.class_id
        LEFT JOIN grades g ON g.course_id = c.id
            AND g.student_id = e.student_id
        WHERE e.student_id = ?
        GROUP BY c.id, c.code, c.name,
                 t.first_name, t.last_name,
                 cl.name
        ORDER BY c.name
    ");
    $stmt->execute([$studentId]);
    $myCourses = $stmt->fetchAll();
}

// Chart data — marks per course
$chartLabels = [];
$chartMarks  = [];
foreach ($myCourses as $course) {
    if ($course['avg_marks']) {
        $chartLabels[] = $course['code'];
        $chartMarks[]  = $course['avg_marks'];
    }
}

// Recent grades
$recentGrades = [];
if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT g.marks, g.grade, g.exam_type,
               g.recorded_at, c.name as course_name
        FROM grades g
        JOIN courses c ON c.id = g.course_id
        WHERE g.student_id = ?
        ORDER BY g.recorded_at DESC
        LIMIT 6
    ");
    $stmt->execute([$studentId]);
    $recentGrades = $stmt->fetchAll();
}

// Attendance per course
$attCourses = [];
if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT c.name as course_name,
               COUNT(a.id) as total,
               SUM(CASE WHEN a.status = 'Present'
                   OR a.status = 'Late'
                   THEN 1 ELSE 0 END) as present
        FROM enrollments e
        JOIN courses c ON c.id = e.course_id
        LEFT JOIN attendance a ON a.course_id = c.id
            AND a.student_id = e.student_id
        WHERE e.student_id = ?
        GROUP BY c.id, c.name
    ");
    $stmt->execute([$studentId]);
    $attCourses = $stmt->fetchAll();
}

// Announcements for student
$stmt = $pdo->query("
    SELECT a.title, a.body, a.created_at,
           u.name as posted_by
    FROM announcements a
    JOIN users u ON u.id = a.posted_by
    WHERE a.is_active = 1
    AND (a.target_role = 'all'
         OR a.target_role = 'student')
    ORDER BY a.created_at DESC
    LIMIT 3
");
$announcements = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- ── Welcome Banner ── -->
<div class="sms-card mb-4"
     style="background:linear-gradient(135deg,
            #1a237e,#283593);
            color:white;border:none;">
    <div class="card-body" style="padding:20px 24px;">
        <div class="d-flex align-items-center gap-3">
            <div style="width:50px;height:50px;
                 background:rgba(255,255,255,0.2);
                 border-radius:50%;
                 display:flex;align-items:center;
                 justify-content:center;
                 font-size:22px;font-weight:700;
                 color:white;flex-shrink:0;">
                <?= strtoupper(substr(
                    $student['first_name'] ?? 'S', 0, 1)) ?>
            </div>
            <div>
                <h5 style="margin:0;color:white;font-weight:700;">
                    Welcome back,
                    <?= e($student['first_name'] ?? authName()) ?>! 👋
                </h5>
                <small style="color:rgba(255,255,255,0.7);">
                    <?= e($student['student_no'] ?? '---') ?> •
                    <?= e($student['class_name'] 
                          ?? 'No class assigned') ?> •
                    <?= ACADEMIC_YEAR ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- ── Stat Cards ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-book-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalCourses ?></h3>
                <p>Enrolled Courses</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $gpa ?: '—' ?></h3>
                <p>Average Marks</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon
                 <?= $attPct < 75 ? 'red' : 'teal' ?>">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $attPct ?>%</h3>
                <p>Attendance</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-megaphone-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalAnnouncements ?></h3>
                <p>Announcements</p>
            </div>
        </div>
    </div>
</div>

<!-- ── Chart + Attendance Row ── -->
<div class="row g-3 mb-4">

    <!-- Marks chart -->
    <div class="col-md-7">
        <div class="sms-card h-100">
            <div class="card-header">
                <h5>
                    <i class="bi bi-bar-chart text-primary"></i>
                    My Marks Per Course
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($chartLabels)): ?>
                <div class="empty-state">
                    <i class="bi bi-bar-chart"></i>
                    <h5>No grades yet</h5>
                    <p>Your marks will appear here
                       once recorded by teachers</p>
                </div>
                <?php else: ?>
                <canvas id="marksChart" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Attendance per course -->
    <div class="col-md-5">
        <div class="sms-card h-100">
            <div class="card-header">
                <h5>
                    <i class="bi bi-calendar-check text-success"></i>
                    Attendance Per Course
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($attCourses)): ?>
                <div class="empty-state">
                    <i class="bi bi-calendar"></i>
                    <p>No attendance records yet</p>
                </div>
                <?php else: ?>
                <?php foreach ($attCourses as $att):
                    $pct = (!empty($att) && $att['total'] > 0)
                        ? round(($att['present']
                                 / $att['total']) * 100)
                        : 0;
                    $color = $pct >= 75
                        ? '#2e7d32' : '#c62828';
                ?>
                <div style="padding:12px 16px;
                     border-bottom:1px solid #f5f5f5;">
                    <div style="display:flex;
                         justify-content:space-between;
                         margin-bottom:5px;">
                        <span style="font-size:13px;
                              font-weight:600;color:#333;">
                            <?= e($att['course_name']) ?>
                        </span>
                        <span style="font-size:12px;
                              font-weight:700;
                              color:<?= $color ?>;">
                            <?= $pct ?>%
                        </span>
                    </div>
                    <div style="height:6px;background:#eee;
                         border-radius:3px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;
                             height:100%;
                             background:<?= $color ?>;
                             border-radius:3px;
                             transition:width 0.5s;">
                        </div>
                    </div>
                    <?php if ($pct < 75): ?>
                    <small style="color:#c62828;font-size:11px;">
                        ⚠️ Below 75% — at risk
                    </small>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Grades + Announcements Row ── -->
<div class="row g-3">

    <!-- Recent grades -->
    <div class="col-md-8">
        <div class="sms-card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-award text-warning"></i>
                    Recent Grades
                </h5>
                <a href="<?= BASE_URL ?>/modules/grades/list.php"
                   class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentGrades)): ?>
                <div class="empty-state">
                    <i class="bi bi-award"></i>
                    <h5>No grades yet</h5>
                    <p>Your grades will appear
                       here once recorded</p>
                </div>
                <?php else: ?>
                <table class="sms-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Exam Type</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentGrades as $g): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= e($g['course_name']) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge-admin">
                                    <?= e($g['exam_type']) ?>
                                </span>
                            </td>
                            <td>
                                <strong>
                                    <?= e($g['marks']) ?>
                                </strong>/100
                            </td>
                            <td>
                                <span class="badge-<?=
                                    $g['grade'] === 'F'
                                    ? 'inactive'
                                    : 'active' ?>">
                                    <?= e($g['grade']) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d M Y',
                                    strtotime(
                                        $g['recorded_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="col-md-4">
        <div class="sms-card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-megaphone text-danger"></i>
                    Announcements
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($announcements)): ?>
                <div class="empty-state">
                    <i class="bi bi-megaphone"></i>
                    <p>No announcements</p>
                </div>
                <?php else: ?>
                <?php foreach ($announcements as $ann): ?>
                <div style="padding:14px 16px;
                     border-bottom:1px solid #f0f0f0;">
                    <div style="font-weight:600;
                         font-size:13px;
                         color:#333;
                         margin-bottom:4px;">
                        <?= e($ann['title']) ?>
                    </div>
                    <div style="font-size:12px;
                         color:#666;
                         margin-bottom:4px;">
                        <?= e(substr($ann['body'],0,80)) ?>...
                    </div>
                    <div style="font-size:11px;color:#999;">
                        <?= e($ann['posted_by']) ?> •
                        <?= date('d M Y',
                            strtotime($ann['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php
$extraScripts = "
<script>
const marksCtx = document.getElementById('marksChart');
if (marksCtx) {
    new Chart(marksCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: " . json_encode($chartLabels) . ",
            datasets: [{
                label: 'Average Marks',
                data: "  . json_encode($chartMarks) . ",
                backgroundColor: [
                    '#1a237e','#e65100','#2e7d32',
                    '#f9a825','#c62828','#0277bd'
                ],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { stepSize: 20 }
                }
            }
        }
    });
}
</script>
";
require_once '../includes/footer.php';
?>