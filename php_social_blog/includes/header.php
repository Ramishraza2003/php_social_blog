<?php $me = currentUser(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>SocialSphere</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <a href="/public/index.php" class="brand">Social<span>Sphere</span></a>
        <form class="nav-search" action="/public/search.php" method="get">
            <input type="text" name="q" placeholder="Search posts or users..."
                   value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>">
        </form>
        <div class="nav-links">
            <?php if ($me): ?>
                <a href="/public/create_post.php">+ New Post</a>
                <a href="/public/profile.php?username=<?= urlencode($me['username']) ?>">
                    <?= e($me['username']) ?>
                </a>
                <?php if ($me['role'] === 'admin'): ?>
                    <a href="/admin/index.php">Admin</a>
                <?php endif; ?>
                <a href="/public/logout.php">Logout</a>
            <?php else: ?>
                <a href="/public/login.php">Login</a>
                <a href="/public/register.php" class="btn-primary-nav">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container">
    <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; ?>
