<?php
require '../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
$data = json_decode(file_get_contents("php://input"));

$name = safe_text($data->name ?? '');
$username = safe_text($data->username ?? '');
$email = filter_var($data->email ?? '', FILTER_SANITIZE_EMAIL);
$password = $data->password ?? '';

if (!$name || !$username || !$email || !$password) json_response(['error' => 'All fields required'], 400);

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $username, $email, $hash]);
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'user';
    track_event($pdo, 'signup');
    json_response(['success' => true]);
} catch (PDOException $e) {
    json_response(['error' => 'Username or email already exists'], 400);
}
