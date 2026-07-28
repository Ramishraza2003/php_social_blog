<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    redirect('/public/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token, please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (attemptLogin($identifier, $password)) {
            redirect('/public/index.php');
        } else {
            $error = 'Invalid credentials or account banned.';
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>

<div class="auth-card">
    <h1>Welcome back</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/public/login.php">
        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">

        <label>Username or Email</label>
        <input type="text" name="identifier" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn-primary">Log In</button>
    </form>

    <p class="auth-switch">Don't have an account? <a href="/public/register.php">Sign up</a></p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
