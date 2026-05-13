<?php
session_start();

$db_host = 'localhost';
$db_name = 'bluepeak'; // Change for InfinityFree
$db_user = 'root';     // Change for InfinityFree
$db_pass = '';         // Change for InfinityFree

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed']));
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) json_response(['error' => 'Unauthorized'], 401);
}

function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') json_response(['error' => 'Forbidden'], 403);
}

function clean_phone($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

function safe_text($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

function track_event($pdo, $event_type, $target_type = null, $target_id = null) {
    $ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR']);
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO analytics_events (event_type, target_type, target_id, user_id, ip_hash) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$event_type, $target_type, $target_id, $user_id, $ip_hash]);
}
?>
