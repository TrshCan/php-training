<?php
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = $_GET['keyword'];
}

$users = $userModel->getUsers($params);

var_dump($_SESSION['csrf_token']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php' ?>
    <div class="container">
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                Hacker example: 
                http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Fullname</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td><?= ($user['id']) ?></td>
                            <td><?= ($user['name']) ?></td>
                            <td><?= ($user['fullname']) ?></td>
                            <td><?= ($user['type']) ?></td>
                            <td>
                                <a href="form_user.php?id=<?= urlencode($user['id']) ?>">
                                    <i class="fa fa-pencil-square-o" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?= urlencode($user['id']) ?>">
                                    <i class="fa fa-eye" title="View"></i>
                                </a>
                                <!-- Delete with CSRF protection -->
                                <form method="POST" action="delete_user.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= ($user['id']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" style="border:none;background:none;padding:0;cursor:pointer;">
                                        <i class="fa fa-eraser" title="Delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark">No users found.</div>
        <?php } ?>
    </div>
</body>
</html>
