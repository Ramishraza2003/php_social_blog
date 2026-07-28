<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
requireAdmin();

$db = getDB();
$userCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$postCount = (int)$db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$commentCount = (int)$db->query("SELECT COUNT(*) FROM comments")->fetchColumn();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<h1>Admin Dashboard</h1>

<div class="admin-stats">
    <div class="stat-box"><strong><?= $userCount ?></strong><span>Users</span></div>
    <div class="stat-box"><strong><?= $postCount ?></strong><span>Posts</span></div>
    <div class="stat-box"><strong><?= $commentCount ?></strong><span>Comments</span></div>
</div>

<div class="admin-links">
    <a href="/admin/users.php" class="btn-primary">Manage Users</a>
    <a href="/admin/posts.php" class="btn-primary">Manage Posts</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
