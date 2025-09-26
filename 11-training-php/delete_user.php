<?php
// delete_user.php (SQLi-only branch, strict id validation)
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

$raw = $_GET['id'] ?? '';

if (!preg_match('/^\d+$/', $raw)) {
    // invalid id format — do not perform any delete
    header('Location: list_users.php?error=invalid_id');
    exit;
}

$id = (int)$raw;
if ($id > 0) {
    $userModel->deleteUserById($id);
}

header('Location: list_users.php');
exit;
