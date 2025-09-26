<?php
// login.php (SQLi-safe handler)
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

// Initialize message
$message = '';

if (!empty($_POST['submit'])) {
    // Basic input normalization
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Optional quick validation
    if ($username === '' || $password === '') {
        $message = 'Login failed';
    } else {
        // Use UserModel::auth which MUST use prepared statements and password_verify
        $user = $userModel->auth($username, $password);

        if ($user) {
            // Login successful - regenerate session id to prevent fixation
            session_regenerate_id(true);
            $_SESSION['id'] = $user[0]['id'];

            // Generic success message (avoid leaking info)
            $_SESSION['message'] = 'Login successful';
            header('Location: list_users.php');
            exit;
        } else {
            // Generic failure message - don't reveal whether user exists
            $message = 'Login failed';
        }
    }
}

// If previous code wrote to session message, show and clear it
if (!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php' ?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading">
                <div class="panel-title">Login</div>
                <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
            </div>

            <div style="padding-top:30px" class="panel-body" >
                <form method="post" class="form-horizontal" role="form">
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="username" value=""
                               placeholder="username or email" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="password" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account! <a href="form_user.php">Sign Up Here</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
