<?php
require '../config.php';
if (!isset($_SESSION['user_id'])) json_response(null);
$stmt = $pdo->prepare("SELECT id, name, username, email, phone, profile_picture, bio, role, is_official FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
json_response($stmt->fetch());
