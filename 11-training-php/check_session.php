<?php
require_once 'models/UserModel.php';

header('Content-Type: application/json');

// Connect Redis
$redis = new Redis();
$redis->connect('web-redis', 6379);

$userModel = new UserModel();

// Grab token (GET or Authorization header)
$headers = getallheaders();
$token = '';
if (!empty($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (!empty($_GET['token'])) {
    $token = $_GET['token'];
}

$response = ['valid' => false];

if ($token) {
    $userId = $redis->get("auth_token:$token");
    if ($userId) {
        $user = $userModel->findUserById($userId);
        if ($user) {
            $response['valid'] = true;
            $response['user'] = [
                'id' => $user['id'] ?? null,
                'name' => $user['name'] ?? null,
                'fullname' => $user['fullname'] ?? null,
                'type' => $user['type'] ?? null
            ];
        }
    }
}

echo json_encode($response);
