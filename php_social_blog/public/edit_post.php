<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$db = getDB();
$me = currentUser();
$postId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    die('Post not found.');
}

if ($me['id'] != $post['user_id'] && $me['role'] !== 'admin') {
    http_response_code(403);
    die('You cannot edit this post.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token, please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');

        if ($title === '') $errors[] = 'Title is required.';
        if ($body === '') $errors[] = 'Content is required.';

        $imageName = $post['image'];
        if (!empty($_FILES['image']['name'])) {
            $uploaded = handleImageUpload($_FILES['image'], __DIR__ . '/uploads/posts');
            if ($uploaded === null) {
                $errors[] = 'Image upload failed.';
            } else {
                if ($post['image']) @unlink(__DIR__ . '/uploads/posts/' . $post['image']);
                $imageName = $uploaded;
            }
        }

        if (empty($errors)) {
            $s = $db->prepare("UPDATE posts SET title = ?, body = ?, image = ? WHERE id = ?");
            $s->execute([$title, $body, $imageName, $postId]);
            flash('success', 'Post updated.');
            redirect('/public/post.php?id=' . $postId);
        }
    }
}

$pageTitle = 'Edit Post';
require __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h1>Edit Post</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="/public/edit_post.php?id=<?= $postId ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">

        <label>Title</label>
        <input type="text" name="title" required value="<?= e($_POST['title'] ?? $post['title']) ?>">

        <label>Content</label>
        <textarea name="body" rows="8" required><?= e($_POST['body'] ?? $post['body']) ?></textarea>

        <?php if ($post['image']): ?>
            <img class="post-image-preview" src="/public/uploads/posts/<?= e($post['image']) ?>" alt="">
        <?php endif; ?>

        <label>Replace Image (optional)</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" class="btn-primary">Save Changes</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
