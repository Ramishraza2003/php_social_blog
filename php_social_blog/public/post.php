<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$me = currentUser();
$postId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare(
    "SELECT p.*, u.username, u.avatar,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count
     FROM posts p JOIN users u ON u.id = p.user_id
     WHERE p.id = ?"
);
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    die('Post not found.');
}

$likedByMe = false;
if ($me) {
    $s = $db->prepare("SELECT 1 FROM likes WHERE post_id = ? AND user_id = ?");
    $s->execute([$postId, $me['id']]);
    $likedByMe = (bool)$s->fetch();
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'])) {
    requireLogin();
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $body = trim($_POST['comment_body']);
        if ($body !== '' && mb_strlen($body) <= 500) {
            $s = $db->prepare("INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)");
            $s->execute([$postId, $me['id'], $body]);
        }
    }
    redirect('/public/post.php?id=' . $postId . '#comments');
}

// Handle post deletion (owner or admin only)
if (isset($_GET['delete']) && $me && ($me['id'] == $post['user_id'] || $me['role'] === 'admin')) {
    if ($post['image']) {
        @unlink(__DIR__ . '/uploads/posts/' . $post['image']);
    }
    $s = $db->prepare("DELETE FROM posts WHERE id = ?");
    $s->execute([$postId]);
    flash('success', 'Post deleted.');
    redirect('/public/index.php');
}

$commentsStmt = $db->prepare(
    "SELECT c.*, u.username, u.avatar FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.post_id = ? ORDER BY c.created_at ASC"
);
$commentsStmt->execute([$postId]);
$comments = $commentsStmt->fetchAll();

$pageTitle = $post['title'];
require __DIR__ . '/../includes/header.php';
?>

<article class="post-full">
    <div class="post-header">
        <img class="avatar-sm" src="/public/uploads/avatars/<?= e($post['avatar']) ?>" alt="">
        <div>
            <a class="post-author" href="/public/profile.php?username=<?= urlencode($post['username']) ?>">
                <?= e($post['username']) ?>
            </a>
            <span class="post-time"><?= timeAgo($post['created_at']) ?></span>
        </div>

        <?php if ($me && ($me['id'] == $post['user_id'] || $me['role'] === 'admin')): ?>
            <div class="post-owner-actions">
                <a href="/public/edit_post.php?id=<?= $postId ?>">Edit</a>
                <a href="?id=<?= $postId ?>&delete=1" onclick="return confirm('Delete this post?');">Delete</a>
            </div>
        <?php endif; ?>
    </div>

    <h1 class="post-title"><?= e($post['title']) ?></h1>

    <?php if ($post['image']): ?>
        <img class="post-image" src="/public/uploads/posts/<?= e($post['image']) ?>" alt="">
    <?php endif; ?>

    <div class="post-body"><?= nl2br(e($post['body'])) ?></div>

    <form method="post" action="/public/like.php" class="like-form">
        <input type="hidden" name="post_id" value="<?= $postId ?>">
        <input type="hidden" name="redirect_to" value="/public/post.php?id=<?= $postId ?>">
        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
        <button type="submit" class="btn-like <?= $likedByMe ? 'liked' : '' ?>">
            ♥ <?= (int)$post['like_count'] ?> Likes
        </button>
    </form>
</article>

<section id="comments" class="comments-section">
    <h2>Comments (<?= count($comments) ?>)</h2>

    <?php if ($me): ?>
        <form method="post" action="/public/post.php?id=<?= $postId ?>" class="comment-form">
            <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
            <textarea name="comment_body" rows="3" maxlength="500" placeholder="Write a comment..." required></textarea>
            <button type="submit" class="btn-primary">Comment</button>
        </form>
    <?php else: ?>
        <p><a href="/public/login.php">Log in</a> to leave a comment.</p>
    <?php endif; ?>

    <?php foreach ($comments as $c): ?>
        <div class="comment">
            <img class="avatar-xs" src="/public/uploads/avatars/<?= e($c['avatar']) ?>" alt="">
            <div>
                <a class="comment-author" href="/public/profile.php?username=<?= urlencode($c['username']) ?>">
                    <?= e($c['username']) ?>
                </a>
                <span class="post-time"><?= timeAgo($c['created_at']) ?></span>
                <p><?= nl2br(e($c['body'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
