<?php
require '../config.php';
require_admin();

$title = safe_text($_POST['title'] ?? '');
$body = safe_text($_POST['body'] ?? '');
$type = safe_text($_POST['post_type'] ?? 'official'); // text_ad, image_ad, official
$filename = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['image']['size'] > 10 * 1024 * 1024) json_response(['error' => 'Too large'], 400);
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/posts/' . $filename);
}

$stmt = $pdo->prepare("INSERT INTO posts (user_id, post_type, title, body, image_url) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $type, $title, $body, $filename]);
json_response(['success' => true]);
