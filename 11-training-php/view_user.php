<?php
require_once 'models/UserModel.php';
$userModel = new UserModel();
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");

$user = null;
$id = null;

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $user = $userModel->findUserById($id);
}

if (!empty($_POST['submit'])) {
    if (!empty($id)) {
        $userModel->updateUser($_POST);
    } else {
        $userModel->insertUser($_POST);
    }
    header('location: list_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>
<div class="container">
    <?php if ($user || empty($id)) { ?>
        <div class="alert alert-warning" role="alert">
            User profile
        </div>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <span><?php echo htmlspecialchars($user[0]['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
            </div>
            <div class="form-group">
                <label for="fullname">Fullname</label>
                <span><?php echo htmlspecialchars($user[0]['fullname'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <span><?php echo htmlspecialchars($user[0]['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
            </div>
        </form>
    <?php } else { ?>
        <div class="alert alert-success" role="alert">
            User not found!
        </div>
    <?php } ?>
</div>
</body>
</html>
