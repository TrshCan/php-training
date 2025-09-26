<?php
// Start the session
session_start();
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");

require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = null;
$_id = null;
$errors = [];

// Read id from GET strictly as integer if provided
if (!empty($_GET['id'])) {
    $_id = (int) $_GET['id'];
    if ($_id > 0) {
        $user = $userModel->findUserById($_id); // returns [$row] or []
    } else {
        $_id = null;
    }
}

// Handle POST (create or update)
if (!empty($_POST['submit'])) {
    // Gather inputs (do minimal server-side validation here)
    $input = [];
    $input['id'] = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $input['name'] = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
    $input['password'] = isset($_POST['password']) ? $_POST['password'] : '';

    // Basic validation
    if ($input['name'] === '') {
        $errors[] = 'Name is required.';
    }
    if (empty($input['id']) && $input['password'] === '') {
        $errors[] = 'Password is required for new users.';
    }
    if (!empty($input['password']) && strlen($input['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        if (!empty($input['id'])) {
            $userModel->updateUser($input);
        } else {
            $userModel->insertUser($input);
        }
        header('Location: list_users.php');
        exit;
    }
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
        <?php if ($user || !isset($_id)) { ?>
            <div class="alert alert-warning" role="alert">User form</div>

            <?php if (!empty($errors)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $err) {
                        echo htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br>';
                    } ?>
                </div>
            <?php } ?>

            <form method="POST" autocomplete="off">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)($_id ?? 0), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input
                        class="form-control"
                        name="name"
                        id="name"
                        placeholder="Name"
                        value="<?php
                            // prefer the posted value (escaped) if present, else DB value
                            if (isset($_POST['name'])) {
                                echo htmlspecialchars($_POST['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            } else {
                                echo !empty($user[0]['name']) ? htmlspecialchars($user[0]['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                            }
                        ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password <?php if (!empty($_id)) echo '(leave blank to keep current)'; ?></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                </div>

                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-success" role="alert">User not found!</div>
        <?php } ?>
    </div>
</body>
</html>
