<?php
require_once 'models/UserModel.php';
require_once 'models/RedisClient.php';

$redis = RedisClient::get();
$userModel = new UserModel();

// Read token from GET param
$token = $_GET['token'] ?? '';

$currentUser = null;
if ($token) {
    $userId = $redis->get("auth_token:$token");
    if ($userId) {
        $currentUser = $userModel->findUserById($userId);
    }
}

$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = $_GET['keyword'];
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const token = localStorage.getItem("auth_token");
    if (!token) {
        window.location.href = "login.php";
    } else {
        const url = new URL(window.location.href);
        if (!url.searchParams.get("token")) {
            url.searchParams.set("token", token);
            window.location.href = url.toString();
        }
    }
});
</script>

<?php if ($currentUser): ?>
    <?php include 'views/header.php' ?>
    <div class="container">
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users!
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
                                <a href="form_user.php?id=<?= urlencode($user['id']) ?>">✏️</a>
                                <a href="view_user.php?token=<?= $token ?>&id=<?= urlencode($user['id']) ?>">👁️</a>
                                <a href="delete_user.php?token=<?= $token ?>&id=<?= urlencode($user['id']) ?>">🗑️</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark">No users found.</div>
        <?php } ?>
    </div>
<?php else: ?>
    <script>
        // Token invalid → nuke and relog
        localStorage.removeItem("auth_token");
        window.location.href = "login.php";
    </script>
<?php endif; ?>
</body>
</html>
