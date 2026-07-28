<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirect('/public/index.php');
}

$me = currentUser();
$postId = (int)($_POST['post_id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT 1 FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $me['id']]);

if ($stmt->fetch()) {
    $del = $db->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $del->execute([$postId, $me['id']]);
} else {
    $ins = $db->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $ins->execute([$postId, $me['id']]);
}

redirect($_POST['redirect_to'] ?? '/public/index.php');
