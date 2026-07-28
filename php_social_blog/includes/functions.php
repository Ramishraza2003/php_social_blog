<?php
/**
 * Shared helper functions
 */

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header("Location: $path");
    exit;
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && $token !== null
        && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/**
 * Handle a validated image upload.
 * Returns the new filename on success, or null on failure.
 */
function handleImageUpload(array $file, string $destDir, int $maxSizeMB = 5): ?string
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > $maxSizeMB * 1024 * 1024) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    // Verify it's actually an image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return null;
    }

    $newName = uniqid('img_', true) . '.' . $ext;
    $destPath = rtrim($destDir, '/') . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return null;
    }

    return $newName;
}

function timeAgo(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $timestamp);
}
