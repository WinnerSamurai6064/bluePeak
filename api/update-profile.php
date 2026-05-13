<?php
require '../config.php';
require_login();
$data = json_decode(file_get_contents("php://input"));

$name = safe_text($data->name ?? '');
$username = safe_text($data->username ?? '');
$phone = safe_text($data->phone ?? '');
$bio = $data->bio ?? '';

if (preg_match('/<script|javascript:|onerror=|onclick=|iframe|object|embed/i', $bio) || strlen($bio) > 120) {
    json_response(['error' => 'Invalid bio content or too long'], 400);
}
$bio = safe_text($bio);

try {
    $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, phone=?, bio=? WHERE id=?");
    $stmt->execute([$name, $username, $phone, $bio, $_SESSION['user_id']]);
    json_response(['success' => true]);
} catch (PDOException $e) {
    json_response(['error' => 'Username might be taken'], 400);
}
