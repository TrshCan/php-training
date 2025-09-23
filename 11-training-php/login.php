<?php
require_once 'models/UserModel.php';
require_once 'configs/env.php';

$redis = new Redis();

try {
    // Try Redis Cloud first
    $redis->connect(
        'tls://' . getenv('REDIS_HOST'),
        getenv('REDIS_PORT')
    );
    $redis->auth(getenv('REDIS_PASS'));
} catch (Exception $e) {
    // Fallback to local Redis
    $redis->connect(getenv('REDIS_LOCAL_HOST'), getenv('REDIS_LOCAL_PORT'));
}

$userModel = new UserModel();
$message = "";

if (!empty($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $userModel->auth($username, $password);

    if ($user) {
        // Generate random token
        $token = bin2hex(random_bytes(32)); 
        $userId = $user[0]['id'];

        // Save to Redis with expiration (1h)
        $redis->setex("auth_token:$token", 3600, $userId);

        // Store in browser localStorage
        echo "<script>
            localStorage.setItem('auth_token', '$token');
            localStorage.setItem('userId', '$userId');
            localStorage.setItem('username', '" . addslashes($username) . "');
            window.location.href = 'list_users.php';
        </script>";
        exit;
    } else {
        $message = "Login failed";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php' ?>

<div class="container">
    <div id="loginbox" style="margin-top:50px;" 
         class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
            </div>

            <div style="padding-top:30px" class="panel-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="post" class="form-horizontal" role="form">
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input type="text" class="form-control"
                               name="username" placeholder="username or email" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input type="password" class="form-control"
                               name="password" placeholder="password" required>
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Login</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account?
                            <a href="form_user.php">Sign Up Here</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
