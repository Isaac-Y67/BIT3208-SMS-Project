<?php
// ================================================
// File: dashboards/teacher.php
// Purpose: Teacher dashboard
// ================================================
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('teacher');

$pageTitle = 'Teacher Dashboard';

// Get teacher profile
$stmt = $pdo->prepare("
    SELECT t.*, u.email 
    FROM teachers t
    JOIN users u ON u.id = t.user_id
    WHERE t.user_id = ?
");
$stmt->execute([authId()]);
$teacher = $stmt->fetch();
$teacherId = $teacher['id'];

// Total courses this teacher handles
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM courses 
    WHERE teacher_id = ? AND status = 'active'
");
$stmt->execute([$teacherId]);
$totalCourses = $stmt->fetchColumn();

// Total students this teacher teaches
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.student_id)
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacherId]);
$totalStudents = $stmt->fetchColumn();

// Total grades recorded by this teacher
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM grades
    WHERE recorded_by = ?
");
$stmt->execute([authId()]);
$totalGrades = $stmt->fetchColumn();

// Attendance marked today
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM attendance a
    JOIN courses c ON c.id = a.course_id
    WHERE c.teacher_id = ? AND a.date = CURDATE()
");
$stmt->execute([$teacherId]);
$todayAttendance = $stmt->fetchColumn();

// My courses with student count
$stmt = $pdo->prepare("
    SELECT c.id, c.code, c.name, c.credits,
           cl.name as class_name,
           COUNT(e.id) as enrolled
    FROM courses c
    LEFT JOIN classes cl ON cl.id = c.class_id
    LEFT JOIN enrollments e ON e.course_id = c.id
    WHERE c.teacher_id = ? AND c.status = 'active'
    GROUP BY c.id
    ORDER BY c.name
");
$stmt->execute([$teacherId]);
$myCourses = $stmt->fetchAll();

// Average marks per course for chart
$stmt = $pdo->prepare("
    SELECT c.name, ROUND(AVG(g.marks),1) as avg_marks
    FROM grades g
    JOIN courses c ON c.id = g.course_id
    WHERE c.teacher_id = ?
    GROUP BY c.id, c.name
");
$stmt->execute([$teacherId]);
$chartData   = $stmt->fetchAll();
$chartLabels = array_column($chartData, 'name');
$chartMarks  = array_column($chartData, 'avg_marks');

// Recent announcements for teachers
$stmt = $pdo->query("
    SELECT a.title, a.body, a.created_at,
           u.name as posted_by
    FROM announcements a
    JOIN users u ON u.id = a.posted_by
    WHERE a.is_active = 1
    AND (a.target_role = 'all' 
         OR a.target_role = 'teacher')
    ORDER BY a.created_at DESC
    LIMIT 3
");
$announcements = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- ── Stat Cards ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-book-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalCourses ?></h3>
                <p>My Courses</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalStudents ?></h3>
                <p>My Students</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalGrades ?></h3>
                <p>Grades Recorded</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon teal">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $todayAttendance ?></h3>
                <p>Marked Today</p>
            </div>
        </div>
    </div>
</div>

<!-- ── Charts + Courses Row ── -->
<div class="row g-3 mb-4">

    <!-- Bar Chart: Average marks per course -->
    <div class="col-md-7">
        <div class="sms-card h-100">
            <div class="card-header">
                <h5>
                    <i class="bi bi-bar-chart text-primary"></i>
                    Average Marks Per Course
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($chartData)): ?>
                <div class="empty-state">
                    <i class="bi bi-bar-chart"></i>
                    <h5>No grades recorded yet</h5>
                    <p>Grades will appear here once entered</p>
                </div>
                <?php else: ?>
                <canvas id="marksChart" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="col-md-5">
        <div class="sms-card h-100">
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
                        <?= e(substr($ann['body'], 0, 80)) ?>...
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

<!-- ── My Courses Table ── -->
<div class="row g-3">
    <div class="col-12">
        <div class="sms-card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-book text-primary"></i>
                    My Courses
                </h5>
                <a href="<?= BASE_URL ?>/modules/attendance/list.php"
                   class="btn btn-sm btn-outline-primary">
                    Mark Attendance
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($myCourses)): ?>
                <div class="empty-state">
                    <i class="bi bi-book"></i>
                    <h5>No courses assigned yet</h5>
                    <p>Contact admin to assign courses</p>
                </div>
                <?php else: ?>
                <table class="sms-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Class</th>
                            <th>Credits</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myCourses as $course): ?>
                        <tr>
                            <td>
                                <span class="badge-admin">
                                    <?= e($course['code']) ?>
                                </span>
                            </td>
                            <td>
                                <strong>
                                    <?= e($course['name']) ?>
                                </strong>
                            </td>
                            <td>
                                <?= e($course['class_name'] ?? '—') ?>
                            </td>
                            <td><?= e($course['credits']) ?></td>
                            <td>
                                <span class="badge-active">
                                    <?= $course['enrolled'] ?> students
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/modules/grades/list.php?course_id=<?= $course['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-bar-chart"></i>
                                    Grades
                                </a>
                                <a href="<?= BASE_URL ?>/modules/attendance/list.php?course_id=<?= $course['id'] ?>"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-calendar-check"></i>
                                    Attendance
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = "
<script>
<?php if (!empty(\$chartData)): ?>
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
            plugins: {
                legend: { display: false }
            },
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
<?php endif; ?>
</script>
";
require_once '../includes/footer.php';
?>