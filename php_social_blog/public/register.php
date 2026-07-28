<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    redirect('/public/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token, please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $errors[] = 'Username must be 3-20 characters (letters, numbers, underscore only).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            [$ok, $result] = registerUser($username, $email, $password, $fullName);
            if ($ok) {
                flash('success', 'Account created! Please log in.');
                redirect('/public/login.php');
            } else {
                $errors[] = $result;
            }
        }
    }
}

$pageTitle = 'Sign Up';
require __DIR__ . '/../includes/header.php';
?>

<div class="auth-card">
    <h1>Create your account</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="/public/register.php">
        <input type="hidden" name="csrf_token" value="<?= e(generateCsrfToken()) ?>">

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>">

        <label>Username</label>
        <input type="text" name="username" required value="<?= e($_POST['username'] ?? '') ?>">

        <label>Email</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">

        <label>Password</label>
        <input type="password" name="password" required minlength="8">

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required minlength="8">

        <button type="submit" class="btn-primary">Sign Up</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="/public/login.php">Log in</a></p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
