<?php
require_once 'models/UserModel.php';

// Connect Redis
$redis = new Redis();
$redis->connect('web-redis', 6379);

$userModel = new UserModel();

// Read token from GET only (JS ensures it’s always there)
$token = $_GET['token'] ?? '';

$currentUser = null;
if ($token) {
    $userId = $redis->get("auth_token:$token");
    if ($userId) {
        $currentUser = $userModel->findUserById($userId);
    }
}

// Don’t redirect here — let JS handle it if no token
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
        // If URL doesn’t already have token, reload with it
        const url = new URL(window.location.href);
        if (!url.searchParams.get("token")) {
            url.searchParams.set("token", token);
            window.location.href = url.toString();
        }
    }
});
</script>

<?php if ($currentUser): ?>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users!
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <th scope="row"><?= $user['id'] ?></th>
                            <td><?= $user['name'] ?></td>
                            <td><?= ($user['fullname']) ?></td>
                            <td><?= ($user['type']) ?></td>
                            <td>
                                <a href="form_user.php?id=<?= $user['id'] ?>">✏️</a>
                                <a href="view_user.php?id=<?= $user['id'] ?>">👁️</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>">🗑️</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark" role="alert">
                No users found.
            </div>
        <?php } ?>
    </div>
<?php endif; ?>
</body>
</html>
