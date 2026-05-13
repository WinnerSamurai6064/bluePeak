<?php
require '../config.php';
require_admin();
$data = json_decode(file_get_contents("php://input"));
$id = (int)($data->id ?? 0);

$res = $pdo->prepare("UPDATE posts SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?");
$res->execute([$id]);

$log = $pdo->prepare("INSERT INTO rollback_logs (admin_id, action_type, target_type, target_id, old_data) VALUES (?, 'restore', 'post', ?, '{}')");
$log->execute([$_SESSION['user_id'], $id]);
json_response(['success' => true]);
