<?php
$siteTitle = 'GameDex - Login';
$activePage = 'Account';

session_start();
require_once __DIR__ . '/config/database.php';
$pdo = DataBase::getInstance()->getConnection();

$error = '';
$success = '';

// Show session-based success messages
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // remove after showing
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Incorrect username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/components/head.php'; ?>
    <link rel="stylesheet" href="styles/login_styles.css?v=<?= filemtime('styles/login_styles.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?>

    <main>
        <div class="login-page-container">
            <form class="login-form" method="POST">
                <h2 class="login-form-title">Login</h2>

                <?php 
                if ($success) echo "<p class='success-message'>$success</p>";
                if ($error) echo "<p class='error-message'>$error</p>"; 
                ?>

                <div class="login-form-group mb-4">
                    <input type="text" class="login-form-control" name="username" placeholder="Username" required>
                </div>

                <div class="login-form-group mb-4">
                    <input type="password" class="login-form-control" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="login-btn-primary mb-3">Sign in</button>

                <div class="text-center mb-3">
                    <span>Don't have an account? </span>
                    <a href="signup.php" class="login-link" style="font-weight: bold;">Sign Up</a>
                </div>

                <div class="text-center">
                    <a href="#" class="login-link">Forgot password?</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
