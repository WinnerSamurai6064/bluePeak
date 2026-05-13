<?php
require '../config.php';
session_destroy();
json_response(['success' => true]);
