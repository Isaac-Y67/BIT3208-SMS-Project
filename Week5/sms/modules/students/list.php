<?php
// ================================================
// File: modules/students/list.php
// Purpose: List all students with search + filter
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireAnyRole(['admin', 'teacher']);

$pageTitle = 'Students';

// ── Search and filter ────────────────────────────
$search   = trim($_GET['search']  ?? '');
$classFilter = trim($_GET['class'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// ── Pagination ───────────────────────────────────
$page  = max(1, intval($_GET['page'] ?? 1));
$limit = ROWS_PER_PAGE;
$offset = ($page - 1) * $limit;

// ── Build query ──────────────────────────────────
$where  = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (s.first_name LIKE ? 
                OR s.last_name LIKE ? 
                OR s.student_no LIKE ?
                OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($classFilter) {
    $where .= " AND s.class_id = ?";
    $params[] = $classFilter;
}

if ($statusFilter) {
    $where .= " AND s.status = ?";
    $params[] = $statusFilter;
}

// Count total for pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM students s
    JOIN users u ON u.id = s.user_id
    $where
");
$countStmt->execute($params);
$totalRows  = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch students
$stmt = $pdo->prepare("
    SELECT s.*, u.email, u.status as user_status,
           c.name as class_name
    FROM students s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN classes c ON c.id = s.class_id
    $where
    ORDER BY s.first_name ASC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get classes for filter dropdown
$classes = $pdo->query("
    SELECT id, name FROM classes 
    ORDER BY name
")->fetchAll();

require_once '../../includes/header.php';
?>

<!-- ── Page Header ── -->
<div class="content-header">
    <div class="d-flex justify-content-between 
                align-items-center">
        <div>
            <h2>
                <i class="bi bi-people me-2 
                   text-primary"></i>Students
            </h2>
            <p>Manage all student records</p>
        </div>
        <?php if (authRole() === 'admin'): ?>
        <a href="<?= BASE_URL ?>/modules/students/add.php"
           class="btn-primary-sms">
            <i class="bi bi-person-plus"></i>
            Add Student
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Search and Filter ── -->
<div class="sms-card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text"
                        class="form-control"
                        name="search"
                        id="liveSearch"
                        placeholder="Search by name, student number or email..."
                        value="<?= e($search) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="class">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>"
                            <?= $classFilter == $class['id']
                                ? 'selected' : '' ?>>
                            <?= e($class['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active"
                            <?= $statusFilter === 'active'
                                ? 'selected' : '' ?>>
                            Active
                        </option>
                        <option value="inactive"
                            <?= $statusFilter === 'inactive'
                                ? 'selected' : '' ?>>
                            Inactive
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit"
                                class="btn-primary-sms w-100">
                            <i class="bi bi-search"></i>
                            Search
                        </button>
                        <a href="<?= BASE_URL ?>/modules/students/list.php"
                           class="btn btn-outline-secondary">
                            ✕
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Students Table ── -->
<div class="sms-card">
    <div class="card-header">
        <h5>
            <i class="bi bi-people text-primary"></i>
            All Students
            <span class="badge bg-primary ms-2">
                <?= $totalRows ?>
            </span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($students)): ?>
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <h5>No students found</h5>
            <p>Try adjusting your search or filters</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="sms-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Student No</th>
                    <th>Class</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $s): ?>
                <tr class="searchable-row">
                    <td><?= $offset + $i + 1 ?></td>
                    <td>
                        <div style="display:flex;
                             align-items:center;
                             gap:10px;">
                            <div style="width:34px;
                                 height:34px;
                                 background:#e8eaf6;
                                 border-radius:50%;
                                 display:flex;
                                 align-items:center;
                                 justify-content:center;
                                 font-weight:700;
                                 color:#1a237e;
                                 font-size:13px;
                                 flex-shrink:0;">
                                <?= strtoupper(
                                    substr($s['first_name'],
                                    0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600;">
                                    <?= e($s['first_name']) ?>
                                    <?= e($s['last_name'])  ?>
                                </div>
                                <div style="font-size:12px;
                                     color:#999;">
                                    <?= e($s['email']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-admin">
                            <?= e($s['student_no']) ?>
                        </span>
                    </td>
                    <td><?= e($s['class_name'] ?? '—') ?></td>
                    <td><?= e($s['phone'] ?? '—') ?></td>
                    <td>
                        <span class="badge-<?= e($s['status']) ?>">
                            <?= ucfirst(e($s['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                        <?php if (authRole() === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/modules/students/edit.php?id=<?= $s['id'] ?>"
                           class="btn btn-sm btn-outline-primary"
                           title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/modules/students/toggle.php?id=<?= $s['id'] ?>"
                           class="btn btn-sm btn-outline-warning confirm-deactivate"
                           title="Toggle Status">
                            <i class="bi bi-toggle-on"></i>
                        </a>
                        <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="padding:16px 20px;
             border-top:1px solid #f0f0f0;">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item
                        <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&class=<?= $classFilter ?>&status=<?= $statusFilter ?>">
                            <?= $p ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>