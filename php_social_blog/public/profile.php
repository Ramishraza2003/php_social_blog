<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$me = currentUser();
$username = trim($_GET['username'] ?? '');

$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    http_response_code(404);
    die('User not found.');
}

$s = $db->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
$s->execute([$profileUser['id']]);
$followerCount = (int)$s->fetchColumn();

$s = $db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$s->execute([$profileUser['id']]);
$followingCount = (int)$s->fetchColumn();

$isFollowing = false;
if ($me) {
    $s = $db->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
    $s->execute([$me['id'], $profileUser['id']]);
    $isFollowing = (bool)$s->fetch();
}

$postsStmt = $db->prepare(
    "SELECT p.*, (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count
     FROM posts p WHERE p.user_id = ? ORDER BY p.created_at DESC"
);
$postsStmt->execute([$profileUser['id']]);
$posts = $postsStmt->fetchAll();

$pageTitle = $profileUser['username'];
require __DIR__ . '/../includes/header.php';
?>

<div class="profile-header">
    <img class="avatar-lg" src="/public/uploads/avatars/<?= e($profileUser['avatar']) ?>" alt="">
    <div>
        <h1><?= e($profileUser['full_name'] ?: $profileUser['username']) ?></h1>
        <p class="profile-username">@<?= e($profileUser['username']) ?></p>
        <?php if ($profileUser['bio']): ?>
            <p class="profile-bio"><?= e($profileUser['bio']) ?></p>
        <?php endif; ?>
        <div class="profile-stats">
            <span><strong><?= count($posts) ?></strong> Posts</span>
            <span><strong><?= $followerCount ?></strong> Followers</span>
            <span><strong><?= $followingCount ?></strong> Following</span>
        </div>

        <?php if ($me && $me['id'] != $profileUser['id']): ?>
            <form method="post" action="/public/follow.php">
                <input type="hidden" name="user_id" value="<?= $profileUser['id'] ?>">
                <input type="hidden" name="redirect_to" value="/public/profile.php?username=<?= urlencode($profileUser['username']) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                <button type="submit" class="btn-primary">
                    <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="feed">
    <?php foreach ($posts as $post): ?>
        <article class="post-card">
            <a class="post-title-link" href="/public/post.php?id=<?= $post['id'] ?>">
                <h2 class="post-title"><?= e($post['title']) ?></h2>
            </a>
            <span class="post-time"><?= timeAgo($post['created_at']) ?></span>
            <p class="post-excerpt"><?= e(mb_strimwidth($post['body'], 0, 180, '...')) ?></p>
            <span class="btn-like"><?= (int)$post['like_count'] ?> ♥</span>
        </article>
    <?php endforeach; ?>

    <?php if (empty($posts)): ?>
        <p class="empty-state">No posts yet.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
