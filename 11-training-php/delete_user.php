<?php
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

// Validate CSRF token
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed");
}

$id = $_POST['id'] ?? null;

if ($id) {
    $userModel->deleteUserById($id);
}

header('Location: list_users.php');
exit;
