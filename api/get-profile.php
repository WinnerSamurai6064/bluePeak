<?php
require '../config.php';
$username = safe_text($_GET['username'] ?? '');
$stmt = $pdo->prepare("SELECT id, name, username, bio, profile_picture, is_official FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
if ($user) {
    track_event($pdo, 'profile_view', 'user', $user['id']);
    json_response($user);
}
json_response(['error' => 'User not found'], 404);
