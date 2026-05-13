<?php
require '../config.php';
$data = json_decode(file_get_contents("php://input"));
if (isset($data->event)) track_event($pdo, safe_text($data->event), safe_text($data->target_type ?? ''), (int)($data->target_id ?? 0));
json_response(['success' => true]);
