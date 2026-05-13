<?php
require '../config.php';
require_login();
$data = json_decode(file_get_contents("php://input"));

$title = safe_text($data->title ?? '');
$body = safe_text($data->body ?? '');
$category = safe_text($data->category ?? '');
$phone = clean_phone($data->whatsapp_phone ?? '');
$message = safe_text($data->whatsapp_message ?? 'Hello, I saw your post on BluePeakConnect.');

if (!$title || !$body) json_response(['error' => 'Title and body required'], 400);

$stmt = $pdo->prepare("INSERT INTO posts (user_id, title, body, category, whatsapp_phone, whatsapp_message) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $title, $body, $category, $phone, $message]);
track_event($pdo, 'post_created', 'post', $pdo->lastInsertId());
json_response(['success' => true]);
