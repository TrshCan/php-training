<?php
require_once 'models/UserModel.php';
require_once 'models/RedisClient.php';

$userModel = new UserModel();
$redis = RedisClient::get();

// Check token from query string
$token = $_GET['token'] ?? null;
if (!$token) {
    die("Unauthorized: Missing token");
}

// Verify token in Redis
$userId = $redis->get("auth_token:$token");
if ($userId === false) {
    var_dump($userId);
    die("Unauthorized: Invalid or expired token");
}

$id = $_GET['id'] ?? null;
if ($userId == $id) {
    die("Unauthorized: You cannot delete your own account");
}
// Proceed with delete
if (!empty($id)) {
    $userModel->deleteUserById($id);
}

// Redirect back to list_users with token preserved
header("Location: list_users.php?token=$token");
exit;
