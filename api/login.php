<?php
require '../config.php';
$data = json_decode(file_get_contents("php://input"));
$login = trim($data->login ?? '');
$password = $data->password ?? '';

$stmt = $pdo->prepare("SELECT id, role, password_hash FROM users WHERE email = ? OR username = ?");
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    track_event($pdo, 'login');
    json_response(['success' => true, 'role' => $user['role']]);
}
json_response(['error' => 'Invalid credentials'], 401);
