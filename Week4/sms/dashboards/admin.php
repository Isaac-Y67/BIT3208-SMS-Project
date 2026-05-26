<?php
// ================================================
// File: dashboards/admin.php
// Purpose: Full admin dashboard with stats+charts
// ================================================
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Only admin can access this page
requireRole('admin');

$pageTitle = 'Admin Dashboard';

// ── Fetch stat card numbers ──────────────────────

// Total students
$stmt = $pdo->query("SELECT COUNT(*) FROM students 
                     WHERE status = 'active'");
$totalStudents = $stmt->fetchColumn();

// Total teachers
$stmt = $pdo->query("SELECT COUNT(*) FROM teachers 
                     WHERE status = 'active'");
$totalTeachers = $stmt->fetchColumn();

// Total courses
$stmt = $pdo->query("SELECT COUNT(*) FROM courses 
                     WHERE status = 'active'");
$totalCourses = $stmt->fetchColumn();

// Active announcements
$stmt = $pdo->query("SELECT COUNT(*) FROM announcements 
                     WHERE is_active = 1");
$totalAnnouncements = $stmt->fetchColumn();

// Total enrollments
$stmt = $pdo->query("SELECT COUNT(*) FROM enrollments");
$totalEnrollments = $stmt->fetchColumn();

// Total classes
$stmt = $pdo->query("SELECT COUNT(*) FROM classes");
$totalClasses = $stmt->fetchColumn();

// ── Chart 1: Students per class ──────────────────
$stmt = $pdo->query("
    SELECT c.name, COUNT(s.id) as total
    FROM classes c
    LEFT JOIN students s ON s.class_id = c.id
        AND s.status = 'active'
    GROUP BY c.id, c.name
    ORDER BY c.name
");
$classData = $stmt->fetchAll();
$classLabels = array_column($classData, 'name');
$classCounts = array_column($classData, 'total');

// ── Chart 2: Grade distribution ──────────────────
$stmt = $pdo->query("
    SELECT grade, COUNT(*) as total
    FROM grades
    GROUP BY grade
    ORDER BY grade
");
$gradeData   = $stmt->fetchAll();
$gradeLabels = array_column($gradeData, 'grade');
$gradeCounts = array_column($gradeData, 'total');

// ── Recent registrations ─────────────────────────
$stmt = $pdo->query("
    SELECT u.name, u.role, u.created_at, u.status
    FROM users u
    ORDER BY u.created_at DESC
    LIMIT 8
");
$recentUsers = $stmt->fetchAll();

// ── Active announcements ─────────────────────────
$stmt = $pdo->query("
    SELECT a.title, a.target_role, a.created_at,
           u.name as posted_by
    FROM announcements a
    JOIN users u ON u.id = a.posted_by
    WHERE a.is_active = 1
    ORDER BY a.created_at DESC
    LIMIT 3
");
$announcements = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- ── Stat Cards Row ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalStudents ?></h3>
                <p>Students</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalTeachers ?></h3>
                <p>Teachers</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-book-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalCourses ?></h3>
                <p>Courses</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
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
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon teal">
                <i class="bi bi-journal-check"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalEnrollments ?></h3>
                <p>Enrollments</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalClasses ?></h3>
                <p>Classes</p>
            </div>
        </div>
    </div>
</div>

<!-- ── Charts Row ── -->
<div class="row g-3 mb-4">

    <!-- Bar Chart: Students per class -->
    <div class="col-md-6">
        <div class="sms-card h-100">
            <div class="card-header">
                <h5>
                    <i class="bi bi-bar-chart-fill text-primary"></i>
                    Students per Class
                </h5>
            </div>
            <div class="card-body">
                <canvas id="classChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Pie Chart: Grade distribution -->
    <div class="col-md-6">
        <div class="sms-card h-100">
            <div class="card-header">
                <h5>
                    <i class="bi bi-pie-chart-fill text-warning"></i>
                    Grade Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="gradeChart" height="220"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- ── Bottom Row ── -->
<div class="row g-3">

    <!-- Recent Users -->
    <div class="col-md-8">
        <div class="sms-card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-clock-history text-primary"></i>
                    Recent Registrations
                </h5>
                <a href="<?= BASE_URL ?>/admin/users.php"
                   class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <table class="sms-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentUsers)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <p>No users yet</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td>
                                <div style="display:flex;
                                     align-items:center;gap:8px;">
                                    <div style="width:30px;height:30px;
                                         background:#e8eaf6;
                                         border-radius:50%;
                                         display:flex;align-items:center;
                                         justify-content:center;
                                         font-weight:700;color:#1a237e;
                                         font-size:12px;">
                                        <?= strtoupper(substr($user['name'],0,1)) ?>
                                    </div>
                                    <?= e($user['name']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge-<?= e($user['role']) ?>">
                                    <?= ucfirst(e($user['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-<?= e($user['status']) ?>">
                                    <?= ucfirst(e($user['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($user['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Announcements + Quick Actions -->
    <div class="col-md-4">

        <!-- Quick Actions -->
        <div class="sms-card mb-3">
            <div class="card-header">
                <h5>
                    <i class="bi bi-lightning-fill text-warning"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/modules/students/add.php"
                       class="btn-primary-sms">
                        <i class="bi bi-person-plus"></i>
                        Add Student
                    </a>
                    <a href="<?= BASE_URL ?>/modules/teachers/add.php"
                       class="btn-accent-sms">
                        <i class="bi bi-person-workspace"></i>
                        Add Teacher
                    </a>
                    <a href="<?= BASE_URL ?>/modules/courses/add.php"
                       class="btn-primary-sms">
                        <i class="bi bi-book"></i>
                        Add Course
                    </a>
                    <a href="<?= BASE_URL ?>/modules/announcements/add.php"
                       class="btn-accent-sms">
                        <i class="bi bi-megaphone"></i>
                        Post Announcement
                    </a>
                </div>
            </div>
        </div>

        <!-- Latest Announcements -->
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
                <div style="padding:12px 16px;
                     border-bottom:1px solid #f0f0f0;">
                    <div style="font-weight:600;font-size:13px;
                         color:#333;margin-bottom:4px;">
                        <?= e($ann['title']) ?>
                    </div>
                    <div style="font-size:11px;color:#999;">
                        <?= e($ann['posted_by']) ?> •
                        <?= date('d M', strtotime($ann['created_at'])) ?> •
                        <span class="badge-<?= e($ann['target_role']) === 'all'
                            ? 'active' : 'student' ?>"
                              style="font-size:10px;">
                            <?= ucfirst(e($ann['target_role'])) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
// Pass chart data to JavaScript
$extraScripts = "
<script>
// ── Bar Chart: Students per Class ──
const classCtx = document.getElementById('classChart').getContext('2d');
new Chart(classCtx, {
    type: 'bar',
    data: {
        labels: " . json_encode($classLabels) . ",
        datasets: [{
            label: 'Students',
            data: "  . json_encode($classCounts) . ",
            backgroundColor: '#1a237e',
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
                ticks: { stepSize: 1 }
            }
        }
    }
});

// ── Pie Chart: Grade Distribution ──
const gradeCtx = document.getElementById('gradeChart').getContext('2d');
new Chart(gradeCtx, {
    type: 'doughnut',
    data: {
        labels: " . json_encode($gradeLabels) . ",
        datasets: [{
            data: "   . json_encode($gradeCounts) . ",
            backgroundColor: [
                '#1a237e','#2e7d32','#e65100',
                '#f9a825','#c62828','#0277bd',
                '#7b1fa2'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 11 } }
            }
        }
    }
});
</script>
";

require_once '../includes/footer.php';
?>