<?php
require_once __DIR__ . '/config/database.php';

$pdo = getDB();

echo "Fetching data from JSONPlaceholder...\n";

// 1. Fetch Users
$usersJson = file_get_contents('https://jsonplaceholder.typicode.com/users');
$apiUsers = json_decode($usersJson, true);

$userIdMap = []; // Maps API user ID to our DB user ID
$defaultPassword = password_hash('password123', PASSWORD_DEFAULT);

echo "Seeding users...\n";
foreach ($apiUsers as $apiUser) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, 'user')");
    // Ensure unique username by appending something if needed, but JSONPlaceholder usernames are usually unique.
    // Also lowercase them for realism.
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $apiUser['username']));
    $email = strtolower($apiUser['email']);
    
    $stmt->execute([
        $username,
        $email,
        $defaultPassword,
        $apiUser['name']
    ]);
    
    // Get the inserted ID. (If it was ignored due to duplicate, we'd need to fetch it, but assuming empty DB except admin)
    if ($stmt->rowCount() > 0) {
        $newId = $pdo->lastInsertId();
        $userIdMap[$apiUser['id']] = $newId;
    } else {
        // Fetch existing
        $stmt2 = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt2->execute([$email]);
        $existing = $stmt2->fetch();
        if ($existing) {
            $userIdMap[$apiUser['id']] = $existing['id'];
        }
    }
}
$insertedUserIds = array_values($userIdMap);

// 2. Fetch Posts
echo "Seeding posts...\n";
$postsJson = file_get_contents('https://jsonplaceholder.typicode.com/posts');
$apiPosts = json_decode($postsJson, true);

$postIdMap = []; // Maps API post ID to our DB post ID

foreach ($apiPosts as $apiPost) {
    if (!isset($userIdMap[$apiPost['userId']])) continue;
    
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)");
    $stmt->execute([
        $userIdMap[$apiPost['userId']],
        $apiPost['title'],
        $apiPost['body']
    ]);
    $postIdMap[$apiPost['id']] = $pdo->lastInsertId();
}

// 3. Fetch Comments
echo "Seeding comments...\n";
$commentsJson = file_get_contents('https://jsonplaceholder.typicode.com/comments');
$apiComments = json_decode($commentsJson, true);

foreach ($apiComments as $apiComment) {
    if (!isset($postIdMap[$apiComment['postId']])) continue;
    
    // JSONPlaceholder comments have an email/name, but no userId.
    // We'll randomly assign a user from our seeded users to author the comment.
    $randomUserId = $insertedUserIds[array_rand($insertedUserIds)];
    
    // Limit body length if needed, schema allows 500 chars
    $body = substr($apiComment['body'], 0, 500);
    
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)");
    $stmt->execute([
        $postIdMap[$apiComment['postId']],
        $randomUserId,
        $body
    ]);
}

// 4. Random Likes and Follows
echo "Seeding likes and follows...\n";
foreach ($postIdMap as $postId) {
    // Randomly 0 to 5 likes per post
    $numLikes = rand(0, 5);
    $likers = (array) array_rand(array_flip($insertedUserIds), $numLikes ?: 1);
    if ($numLikes === 0) $likers = [];
    
    foreach ($likers as $likerId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$postId, $likerId]);
    }
}

foreach ($insertedUserIds as $followerId) {
    // Randomly follow 1 to 4 users
    $numFollows = rand(1, 4);
    $followees = (array) array_rand(array_flip($insertedUserIds), $numFollows);
    foreach ($followees as $followingId) {
        if ($followerId !== $followingId) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)");
            $stmt->execute([$followerId, $followingId]);
        }
    }
}

echo "Database seeded successfully!\n";
