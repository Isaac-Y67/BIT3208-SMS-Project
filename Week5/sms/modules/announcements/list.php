<?php
// ================================================
// File: modules/announcements/list.php
// Purpose: View and manage announcements
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireLogin();

$pageTitle = 'Announcements';
$role      = authRole();

// Handle delete/deactivate
if ($_GET['action'] ?? '' === 'toggle') {
    $annId = intval($_GET['id'] ?? 0);
    if ($annId) {
        $stmt = $pdo->prepare("
            UPDATE announcements
            SET is_active = !is_active
            WHERE id = ?
        ");
        $stmt->execute([$annId]);
        setFlash('success',
            'Announcement updated successfully!');
    }
    header('Location: ' . BASE_URL .
           '/modules/announcements/list.php');
    exit;
}

if ($_GET['action'] ?? '' === 'delete') {
    if ($role === 'admin') {
        $annId = intval($_GET['id'] ?? 0);
        if ($annId) {
            $stmt = $pdo->prepare("
                DELETE FROM announcements
                WHERE id = ?
            ");
            $stmt->execute([$annId]);
            setFlash('success',
                'Announcement deleted successfully!');
        }
    }
    header('Location: ' . BASE_URL .
           '/modules/announcements/list.php');
    exit;
}

// Fetch announcements based on role
if ($role === 'admin') {
    $announcements = $pdo->query("
        SELECT a.*, u.name as posted_by_name
        FROM announcements a
        JOIN users u ON u.id = a.posted_by
        ORDER BY a.created_at DESC
    ")->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as posted_by_name
        FROM announcements a
        JOIN users u ON u.id = a.posted_by
        WHERE a.is_active = 1
        AND (a.target_role = 'all'
             OR a.target_role = ?)
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$role]);
    $announcements = $stmt->fetchAll();
}

require_once '../../includes/header.php';
?>

<!-- ── Page Header ── -->
<div class="content-header">
    <div class="d-flex justify-content-between
                align-items-center">
        <div>
            <h2>
                <i class="bi bi-megaphone me-2
                   text-primary"></i>Announcements
            </h2>
            <p>System announcements and notices</p>
        </div>
        <?php if (in_array($role,
                  ['admin','teacher'])): ?>
        <a href="<?= BASE_URL ?>/modules/announcements/add.php"
           class="btn-primary-sms">
            <i class="bi bi-plus-lg"></i>
            Post Announcement
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Announcements List ── -->
<?php if (empty($announcements)): ?>
<div class="sms-card">
    <div class="empty-state">
        <i class="bi bi-megaphone"></i>
        <h5>No announcements yet</h5>
        <p>Check back later for updates</p>
    </div>
</div>

<?php else: ?>
<?php foreach ($announcements as $ann): ?>
<div class="sms-card mb-3"
     style="<?= !$ann['is_active']
               ? 'opacity:0.6;' : '' ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between
                    align-items-start">
            <div style="flex:1;">

                <!-- Title + badges -->
                <div class="d-flex align-items-center
                            gap-2 mb-2 flex-wrap">
                    <h5 style="margin:0;font-size:16px;
                         font-weight:700;color:#333;">
                        <?= e($ann['title']) ?>
                    </h5>
                    <span class="badge-<?=
                        $ann['target_role'] === 'all'
                        ? 'active'
                        : ($ann['target_role'] === 'student'
                           ? 'student' : 'admin')
                        ?>"
                        style="font-size:11px;">
                        <?= ucfirst(
                            e($ann['target_role'])) ?>
                    </span>
                    <?php if (!$ann['is_active']): ?>
                    <span class="badge-inactive"
                          style="font-size:11px;">
                        Archived
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Body -->
                <p style="color:#555;font-size:14px;
                     margin-bottom:10px;
                     line-height:1.6;">
                    <?= e($ann['body']) ?>
                </p>

                <!-- Meta -->
                <div style="font-size:12px;color:#999;">
                    <i class="bi bi-person me-1"></i>
                    Posted by
                    <strong>
                        <?= e($ann['posted_by_name']) ?>
                    </strong>
                    &nbsp;•&nbsp;
                    <i class="bi bi-clock me-1"></i>
                    <?= date('d M Y, g:i A',
                        strtotime($ann['created_at'])) ?>
                </div>
            </div>

            <!-- Action buttons admin only -->
            <?php if ($role === 'admin'): ?>
            <div class="d-flex gap-2 ms-3">
                <a href="<?= BASE_URL ?>/modules/announcements/list.php?action=toggle&id=<?= $ann['id'] ?>"
                   class="btn btn-sm btn-outline-warning"
                   title="<?= $ann['is_active']
                              ? 'Archive' : 'Activate' ?>">
                    <i class="bi bi-toggle-<?=
                        $ann['is_active'] ? 'on' : 'off'
                        ?>"></i>
                </a>
                <a href="<?= BASE_URL ?>/modules/announcements/list.php?action=delete&id=<?= $ann['id'] ?>"
                   class="btn btn-sm btn-outline-danger
                          confirm-delete"
                   title="Delete">
                    <i class="bi bi-trash"></i>
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>