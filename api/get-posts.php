<?php
require '../config.php';
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$query = "SELECT p.*, u.name, u.username, u.profile_picture, u.is_official 
          FROM posts p JOIN users u ON p.user_id = u.id ";
$query .= $is_admin ? "ORDER BY p.created_at DESC" : "WHERE p.is_deleted = 0 ORDER BY p.created_at DESC";

$stmt = $pdo->query($query);
json_response($stmt->fetchAll());
