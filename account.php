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
$stmt = $pdo->prepare("SELECT username, email, preferred_currency FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user platforms
$stmtPlatforms = $pdo->query("SELECT id, name FROM platforms ORDER BY name");
$allPlatforms = $stmtPlatforms->fetchAll(PDO::FETCH_ASSOC);

$stmtUserPlatforms = $pdo->prepare("SELECT platform_id FROM user_platforms WHERE user_id = ?");
$stmtUserPlatforms->execute([$_SESSION['user_id']]);
$userPlatforms = $stmtUserPlatforms->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/components/head.php'; ?>
    <link rel="stylesheet" href="styles/account_styles.css?v=<?= filemtime('styles/account_styles.css') ?>">
    <title>GameDex - Account</title>
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?>

    <main>
        <div class="login-page-container">
            <form class="login-form" action="update_account.php" method="POST">
                <h2 class="login-form-title">Your Account</h2>

                <!-- Username -->
                <div class="login-form-group mb-4">
                    <label for="username">Username</label>
                    <input type="text" class="login-form-control" 
                           id="username"
                           name="username" 
                           value="<?= htmlspecialchars($user['username']) ?>" 
                           placeholder="Enter your username">
                </div>

                <!-- Email -->
                <div class="login-form-group mb-4">
                    <label for="email">Email</label>
                    <input type="email" class="login-form-control" 
                           id="email"
                           name="email" 
                           value="<?= htmlspecialchars($user['email']) ?>" 
                           placeholder="Enter your email">
                </div>

                <!-- New password -->
                <div class="login-form-group mb-4">
                    <label for="password">New Password</label>
                    <input type="password" class="login-form-control" 
                           id="password"
                           name="password" 
                           placeholder="Enter new password">
                </div>

                <!-- Repeat new password -->
                <div class="login-form-group mb-4">
                    <label for="password_repeat">Repeat New Password</label>
                    <input type="password" class="login-form-control" 
                           id="password_repeat"
                           name="password_repeat" 
                           placeholder="Repeat new password">
                </div>

                <!-- Preferred currency AUD only -->
                <div class="login-form-group mb-4">
                    <label for="preferred_currency">Preferred Currency</label>
                    <select class="login-form-control" name="preferred_currency" id="preferred_currency">
                        <option value="AUD" selected>AUD</option>
                    </select>
                </div>

                <!-- Platforms checkboxes -->
                <div class="login-form-group mb-4">
                    <label>Owned consoles and platforms:</label>
                    <div>
                        <?php foreach ($allPlatforms as $platform): ?>
                            <label>
                                <input type="checkbox" name="platforms[]" value="<?= $platform['name'] ?>"
                                    <?= in_array($platform['id'], $userPlatforms) ? 'checked' : '' ?>>
                                <?= $platform['name'] ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Save Changes + Delete Account -->
                <div class="button-row text-center mb-3">
                    <button type="submit" class="login-btn-primary">Save Changes</button>
                    <a href="delete_account.php" class="logout-link">Delete Account</a>
                </div>

                <!-- Logout -->
                <div class="text-center mb-3">
                    <a href="logout.php" class="logout-link">Log out</a>
                </div>


            </form>
        </div>
    </main>
</body>
</html>
