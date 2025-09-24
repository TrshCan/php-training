<?php
session_start();

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If POST request, process logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed");
    }

    session_destroy();
    header('location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Logout</title></head>
<body>
    <form method="POST" action="logout.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
</body>
</html>
