<?php
/**
 * Authentication helpers
 */
require_once __DIR__ . '/../config/database.php';

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $stmt = getDB()->prepare("SELECT id, username, email, full_name, bio, avatar, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Please log in to continue.');
        redirect('/public/login.php');
    }
}

function requireAdmin(): void
{
    $user = currentUser();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied: admin only.');
    }
}

function registerUser(string $username, string $email, string $password, string $fullName): array
{
    $db = getDB();

    // Uniqueness checks
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return [false, 'Username or email already taken.'];
    }

    if (strlen($password) < 8) {
        return [false, 'Password must be at least 8 characters.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare(
        "INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$username, $email, $hash, $fullName]);

    return [true, $db->lastInsertId()];
}

function attemptLogin(string $emailOrUsername, string $password): bool
{
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT id, password_hash, status FROM users WHERE email = ? OR username = ?"
    );
    $stmt->execute([$emailOrUsername, $emailOrUsername]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] === 'banned') {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    // Prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];

    return true;
}

function logoutUser(): void
{
    $_SESSION = [];
    session_destroy();
}
