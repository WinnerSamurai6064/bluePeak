<?php
require '../config.php';
$data = json_decode(file_get_contents("php://input"));
$name = safe_text($data->name ?? '');
$email = safe_text($data->email ?? '');
$message = safe_text($data->message ?? '');

if (strlen($message) > 500 || !$message) json_response(['error' => 'Message is required and must be under 500 chars'], 400);

$stmt = $pdo->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $message]);
track_event($pdo, 'feedback_sent');
json_response(['success' => true]);
