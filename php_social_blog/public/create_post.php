<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$me = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token, please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');

        if ($title === '' || mb_strlen($title) > 200) {
            $errors[] = 'Title is required (max 200 characters).';
        }
        if ($body === '') {
            $errors[] = 'Post content cannot be empty.';
        }

        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $imageName = handleImageUpload($_FILES['image'], __DIR__ . '/uploads/posts');
            if ($imageName === null) {
                $errors[] = 'Image upload failed. Use jpg/png/gif/webp under 5MB.';
            }
        }

        if (empty($errors)) {
            $stmt = getDB()->prepare(
                "INSERT INTO posts (user_id, title, body, image) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$me['id'], $title, $body, $imageName]);
            flash('success', 'Post published!');
            redirect('/public/post.php?id=' . getDB()->lastInsertId());
        }
    }
}

$pageTitle = 'New Post';
require __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h1>Create a New Post</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="/public/create_post.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">

        <label>Title</label>
        <input type="text" name="title" required maxlength="200" value="<?= e($_POST['title'] ?? '') ?>">

        <label>Content</label>
        <textarea name="body" rows="8" required><?= e($_POST['body'] ?? '') ?></textarea>

        <label>Image (optional)</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" class="btn-primary">Publish</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
