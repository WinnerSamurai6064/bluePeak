<?php
require '../config.php';
require_admin();
$stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC LIMIT 50");
json_response($stmt->fetchAll());
