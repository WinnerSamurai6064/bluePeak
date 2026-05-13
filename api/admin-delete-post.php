<?php
require '../config.php';
require_admin();
$data = json_decode(file_get_contents("php://input"));
$id = (int)($data->id ?? 0);

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if ($post) {
    $log = $pdo->prepare("INSERT INTO rollback_logs (admin_id, action_type, target_type, target_id, old_data) VALUES (?, 'delete', 'post', ?, ?)");
    $log->execute([$_SESSION['user_id'], $id, json_encode($post)]);

    $del = $pdo->prepare("UPDATE posts SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?");
    $del->execute([$_SESSION['user_id'], $id]);
}
json_response(['success' => true]);
