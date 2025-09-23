<?php
require_once 'models/RedisClient.php';

$redis = RedisClient::get();

// Token should be sent by the frontend (e.g., via AJAX or fetch header)
$headers = getallheaders();
$token = $headers['Authorization'] ?? null;

if ($token) {
    // Remove Redis entry
    $redis->del("auth_token:$token");
}

// Return JSON response (frontend will handle redirect + clear localStorage)
header('Content-Type: application/json');
echo json_encode(['status' => 'logged_out']);
exit;
