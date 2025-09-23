<?php
require_once 'models/UserModel.php';
require_once 'models/RedisClient.php';

$userModel = new UserModel();
$redis = RedisClient::get();

$user = null;
$_id = $_GET['id'] ?? null;

if (!empty($_id)) {
    $user = $userModel->findUserById($_id); // Editing existing user
}

if (!empty($_POST['submit'])) {
    if (!empty($_id)) {
        $userModel->updateUser($_POST);
        $userId = $_id;
    } else {
        $userId = $userModel->insertUser($_POST);
    }

    // ✅ Generate token and save in Redis (1 hour expiry)
    $token = bin2hex(random_bytes(32));
    $redis->setex("auth_token:$token", 3600, $userId);

    // ✅ Send token + user info to client via localStorage
    echo "<script>
        localStorage.setItem('auth_token', '$token');
        localStorage.setItem('userId', '$userId');
        localStorage.setItem('username', '" . addslashes($_POST['name']) . "');
        window.location.href = 'list_users.php?token=$token';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php'; ?>
</head>
<body>
<?php include 'views/header.php'; ?>

<div class="container">
    <?php if ($user || !isset($_id)) { ?>
        <div class="alert alert-warning" role="alert">
            User form
        </div>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($_id ?? ''); ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <input class="form-control" name="name" placeholder="Name"
                       value='<?php echo !empty($user[0]['name']) ? htmlspecialchars($user[0]['name']) : ""; ?>'>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password">
            </div>

            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php } else { ?>
        <div class="alert alert-success" role="alert">
            User not found!
        </div>
    <?php } ?>
</div>

</body>
</html>
