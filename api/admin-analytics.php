<?php
require '../config.php';
require_admin();

$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['posts'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE is_deleted=0")->fetchColumn();
$stats['feedback'] = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$stats['clicks'] = $pdo->query("SELECT COUNT(*) FROM analytics_events WHERE event_type='whatsapp_click'")->fetchColumn();

$stats['new_users_24h'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();
$stats['new_posts_24h'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();

json_response($stats);
