<?php
require '../config.php';
require_login();

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'Upload failed'], 400);
}

$file = $_FILES['image'];
if ($file['size'] > 10 * 1024 * 1024) json_response(['error' => 'File too large (Max 10MB)'], 400);

$allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
$mime = mime_content_type($file['tmp_name']);
if (!in_array($mime, $allowed_types)) json_response(['error' => 'Invalid file type'], 400);

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$filepath = '../uploads/profiles/' . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$filename, $_SESSION['user_id']]);
    json_response(['success' => true, 'url' => $filename]);
}
json_response(['error' => 'Failed to save file'], 500);
