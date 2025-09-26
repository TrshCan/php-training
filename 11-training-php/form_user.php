<?php
// form_user.php (SQLi-safe + XSS output escaping)
// Start the session
session_start();

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
    // Normalize input
    $input = [];
    $input['id'] = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $input['name'] = isset($_POST['name']) ? trim($_POST['name']) : '';
    $input['password'] = isset($_POST['password']) ? $_POST['password'] : '';
    // optional extra fields
    $input['fullname'] = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $input['type'] = isset($_POST['type']) ? trim($_POST['type']) : 'user';

    // Basic validation
    if ($input['name'] === '') {
        $errors[] = 'Name is required.';
    }
    // Optionally require a password for new users
    if (empty($input['id']) && $input['password'] === '') {
        $errors[] = 'Password is required for new users.';
    }
    // Password length check (optional)
    if (!empty($input['password']) && strlen($input['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        // For SQLi-safety, UserModel methods use prepared statements.
        if (!empty($input['id'])) {
            // updateUser expects an array including id, name, password, etc.
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
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $err) {
                    echo htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br>';
                } ?>
            </div>
        <?php } ?>

        <?php if ($user || !isset($_id)) { ?>
            <div class="alert alert-warning" role="alert">
                User form
            </div>
            <form method="POST" autocomplete="off">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars((int)($_id ?? 0), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input 
                        class="form-control" 
                        name="name" 
                        id="name"
                        placeholder="Name" 
                        value="<?php
                            // show posted value if validation failed, else show db value
                            if (!empty($_POST['name'])) {
                                echo htmlspecialchars($_POST['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            } else {
                                echo !empty($user[0]['name']) ? htmlspecialchars($user[0]['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                            }
                        ?>">
                </div>

                <div class="form-group">
                    <label for="fullname">Fullname</label>
                    <input
                        class="form-control"
                        name="fullname"
                        id="fullname"
                        placeholder="Full name"
                        value="<?php
                            if (!empty($_POST['fullname'])) {
                                echo htmlspecialchars($_POST['fullname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            } else {
                                echo !empty($user[0]['fullname']) ? htmlspecialchars($user[0]['fullname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                            }
                        ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password <?php if (!empty($_id)) echo '(leave blank to keep current)'; ?></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                </div>

                <div class="form-group">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="form-control">
                        <?php
                        $currentType = '';
                        if (!empty($_POST['type'])) {
                            $currentType = $_POST['type'];
                        } elseif (!empty($user[0]['type'])) {
                            $currentType = $user[0]['type'];
                        }
                        $types = ['user' => 'User', 'admin' => 'Admin'];
                        foreach ($types as $k => $label) {
                            $sel = ($currentType === $k) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($k, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\" $sel>" . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
                        }
                        ?>
                    </select>
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
