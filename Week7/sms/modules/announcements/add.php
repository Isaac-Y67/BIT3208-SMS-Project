<?php
// ================================================
// File: modules/announcements/add.php
// Purpose: Post a new announcement
// ================================================
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

requireAnyRole(['admin', 'teacher']);

$pageTitle = 'Post Announcement';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title']       ?? '');
    $body        = trim($_POST['body']        ?? '');
    $target_role = trim($_POST['target_role'] ?? 'all');

    if (empty($title))
        $errors[] = 'Title is required.';
    if (empty($body))
        $errors[] = 'Message body is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO announcements
            (title, body, target_role,
             posted_by, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $title, $body,
            $target_role, authId()
        ]);
        setFlash('success',
            'Announcement posted successfully!');
        header('Location: ' . BASE_URL .
               '/modules/announcements/list.php');
        exit;
    }
}

require_once '../../includes/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between
                align-items-center">
        <div>
            <h2>
                <i class="bi bi-megaphone me-2
                   text-primary"></i>
                Post Announcement
            </h2>
            <p>Create a new system announcement</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/announcements/list.php"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
        <li><?= e($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="sms-card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-megaphone
                       text-primary"></i>
                    New Announcement
                </h5>
            </div>
            <div class="card-body">
                <form method="POST"
                      action=""
                      class="sms-form">

                    <div class="mb-3">
                        <label class="form-label">
                            Title *
                        </label>
                        <input type="text"
                            class="form-control"
                            name="title"
                            placeholder="Announcement title"
                            value="<?= e($_POST['title'] ?? '') ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Message *
                        </label>
                        <textarea
                            class="form-control"
                            name="body"
                            rows="5"
                            placeholder="Write your announcement here..."
                            required
                            ><?= e($_POST['body'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            Target Audience *
                        </label>
                        <select class="form-select"
                                name="target_role">
                            <option value="all"
                                <?= ($_POST['target_role']
                                     ?? '') === 'all'
                                    ? 'selected' : '' ?>>
                                Everyone
                            </option>
                            <?php if (authRole()
                                      === 'admin'): ?>
                            <option value="teacher"
                                <?= ($_POST['target_role']
                                     ?? '') === 'teacher'
                                    ? 'selected' : '' ?>>
                                Teachers Only
                            </option>
                            <?php endif; ?>
                            <option value="student"
                                <?= ($_POST['target_role']
                                     ?? '') === 'student'
                                    ? 'selected' : '' ?>>
                                Students Only
                            </option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit"
                                class="btn-primary-sms">
                            <i class="bi bi-megaphone"></i>
                            Post Announcement
                        </button>
                        <a href="<?= BASE_URL ?>/modules/announcements/list.php"
                           class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>