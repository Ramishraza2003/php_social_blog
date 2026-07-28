<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$me = currentUser();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$sql = "SELECT p.*, u.username, u.avatar,
               (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
               (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
               " . ($me ? ", (SELECT COUNT(*) FROM likes l2 WHERE l2.post_id = p.id AND l2.user_id = :me) AS liked_by_me" : "") . "
        FROM posts p
        JOIN users u ON u.id = p.user_id
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
if ($me) $stmt->bindValue(':me', $me['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$totalPosts = (int)$db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalPages = max(1, (int)ceil($totalPosts / $perPage));

$pageTitle = 'Feed';
require __DIR__ . '/../includes/header.php';
?>

<div class="feed">
    <h1 class="feed-heading">Latest Posts</h1>

    <?php if (empty($posts)): ?>
        <p class="empty-state">No posts yet. Be the first to <a href="/public/create_post.php">share something</a>!</p>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <article class="post-card">
            <div class="post-header">
                <img class="avatar-sm" src="/public/uploads/avatars/<?= e($post['avatar']) ?>" alt="">
                <div>
                    <a class="post-author" href="/public/profile.php?username=<?= urlencode($post['username']) ?>">
                        <?= e($post['username']) ?>
                    </a>
                    <span class="post-time"><?= timeAgo($post['created_at']) ?></span>
                </div>
            </div>

            <a class="post-title-link" href="/public/post.php?id=<?= (int)$post['id'] ?>">
                <h2 class="post-title"><?= e($post['title']) ?></h2>
            </a>

            <?php if ($post['image']): ?>
                <img class="post-image" src="/public/uploads/posts/<?= e($post['image']) ?>" alt="">
            <?php endif; ?>

            <p class="post-excerpt"><?= e(mb_strimwidth($post['body'], 0, 220, '...')) ?></p>

            <div class="post-actions">
                <form method="post" action="/public/like.php" class="like-form">
                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">
                    <button type="submit" class="btn-like <?= !empty($post['liked_by_me']) ? 'liked' : '' ?>">
                        ♥ <?= (int)$post['like_count'] ?>
                    </button>
                </form>
                <a href="/public/post.php?id=<?= (int)$post['id'] ?>#comments" class="btn-comment">
                    💬 <?= (int)$post['comment_count'] ?>
                </a>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
