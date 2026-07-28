<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
requireAdmin();

$db = getDB();
$me = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $targetId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($targetId !== $me['id']) { // admin can't ban/demote self
        if ($action === 'toggle_ban') {
            $db->prepare("UPDATE users SET status = IF(status='active','banned','active') WHERE id = ?")
               ->execute([$targetId]);
        } elseif ($action === 'toggle_role') {
            $db->prepare("UPDATE users SET role = IF(role='admin','user','admin') WHERE id = ?")
               ->execute([$targetId]);
        }
    }
    redirect('/admin/users.php');
}

$users = $db->query("SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Users';
require __DIR__ . '/../includes/header.php';
?>

<h1>Manage Users</h1>

<table class="admin-table">
    <thead>
        <tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['username']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><?= e($u['status']) ?></td>
            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
                <?php if ($u['id'] != $me['id']): ?>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="toggle_ban">
                        <button type="submit" class="btn-small">
                            <?= $u['status'] === 'active' ? 'Ban' : 'Unban' ?>
                        </button>
                    </form>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="toggle_role">
                        <button type="submit" class="btn-small">
                            <?= $u['role'] === 'admin' ? 'Demote' : 'Make Admin' ?>
                        </button>
                    </form>
                <?php else: ?>
                    (you)
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
