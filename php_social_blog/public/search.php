<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$q = trim($_GET['q'] ?? '');
$posts = [];
$users = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    $stmt = $db->prepare(
        "SELECT p.*, u.username FROM posts p JOIN users u ON u.id = p.user_id
         WHERE p.title LIKE ? OR p.body LIKE ? ORDER BY p.created_at DESC LIMIT 20"
    );
    $stmt->execute([$like, $like]);
    $posts = $stmt->fetchAll();

    $stmt = $db->prepare(
        "SELECT id, username, full_name, avatar FROM users
         WHERE username LIKE ? OR full_name LIKE ? LIMIT 10"
    );
    $stmt->execute([$like, $like]);
    $users = $stmt->fetchAll();
}

$pageTitle = 'Search';
require __DIR__ . '/../includes/header.php';
?>

<h1>Search results for "<?= e($q) ?>"</h1>

<h2>Users</h2>
<?php if (empty($users)): ?>
    <p class="empty-state">No users found.</p>
<?php endif; ?>
<div class="user-results">
    <?php foreach ($users as $u): ?>
        <a class="user-result" href="/public/profile.php?username=<?= urlencode($u['username']) ?>">
            <img class="avatar-sm" src="/public/uploads/avatars/<?= e($u['avatar']) ?>" alt="">
            <?= e($u['full_name'] ?: $u['username']) ?> (@<?= e($u['username']) ?>)
        </a>
    <?php endforeach; ?>
</div>

<h2>Posts</h2>
<?php if (empty($posts)): ?>
    <p class="empty-state">No posts found.</p>
<?php endif; ?>
<div class="feed">
    <?php foreach ($posts as $post): ?>
        <article class="post-card">
            <a class="post-title-link" href="/public/post.php?id=<?= $post['id'] ?>">
                <h2 class="post-title"><?= e($post['title']) ?></h2>
            </a>
            <span class="post-time">by <?= e($post['username']) ?> · <?= timeAgo($post['created_at']) ?></span>
            <p class="post-excerpt"><?= e(mb_strimwidth($post['body'], 0, 180, '...')) ?></p>
        </article>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
