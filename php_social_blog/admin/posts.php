<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
requireAdmin();

$db = getDB();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT image FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    if ($row = $stmt->fetch()) {
        if ($row['image']) @unlink(__DIR__ . '/../public/uploads/posts/' . $row['image']);
        $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    }
    redirect('/admin/posts.php');
}

$posts = $db->query(
    "SELECT p.id, p.title, p.created_at, u.username
     FROM posts p JOIN users u ON u.id = p.user_id
     ORDER BY p.created_at DESC"
)->fetchAll();

$pageTitle = 'Manage Posts';
require __DIR__ . '/../includes/header.php';
?>

<h1>Manage Posts</h1>

<table class="admin-table">
    <thead><tr><th>Title</th><th>Author</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
        <tr>
            <td><a href="/public/post.php?id=<?= $p['id'] ?>"><?= e($p['title']) ?></a></td>
            <td><?= e($p['username']) ?></td>
            <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
            <td>
                <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this post?');" class="btn-small">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
