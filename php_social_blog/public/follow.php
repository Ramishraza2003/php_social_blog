<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirect('/public/index.php');
}

$me = currentUser();
$targetId = (int)($_POST['user_id'] ?? 0);
$db = getDB();

if ($targetId === $me['id']) {
    redirect('/public/index.php'); // can't follow yourself
}

$stmt = $db->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
$stmt->execute([$me['id'], $targetId]);

if ($stmt->fetch()) {
    $del = $db->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $del->execute([$me['id'], $targetId]);
} else {
    $ins = $db->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
    $ins->execute([$me['id'], $targetId]);
}

redirect($_POST['redirect_to'] ?? '/public/index.php');
