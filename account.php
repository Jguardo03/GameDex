<?php
// account.php
session_start();
require_once __DIR__ . '/config/database.php'; // Path to your database connection

$pdo = DataBase::getInstance()->getConnection();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/components/head.php'; ?>
    <link rel="stylesheet" href="styles/account_styles.css?v=<?= filemtime('styles/login_styles.css') ?>">
    <title>GameDex - Account</title>
    <style>
        /* Optional quick styling for labels */
        .login-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?>

    <main>
        <div class="login-page-container">
            <form class="login-form">
                <h2 class="login-form-title">Your Account</h2>

                <!-- Username (editable) -->
                <div class="login-form-group mb-4">
                    <label for="username">Username</label>
                    <input type="text" class="login-form-control" 
                           id="username"
                           name="username" 
                           value="<?= htmlspecialchars($user['username']) ?>" 
                           placeholder="Enter your username">
                </div>

                <!-- Email (editable) -->
                <div class="login-form-group mb-4">
                    <label for="email">Email</label>
                    <input type="email" class="login-form-control" 
                           id="email"
                           name="email" 
                           value="<?= htmlspecialchars($user['email']) ?>" 
                           placeholder="Enter your email">
                </div>

                <!-- Logout button -->
                <div class="text-center mb-3">
                    <a href="logout.php" class="logout-link">Log out</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
