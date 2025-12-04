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

$userId = $_SESSION['user_id'];

// Hantera uppdatering om formuläret skickas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    // Hämta användarens aktuella lösenord hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        $_SESSION['error_message'] = "Current password is incorrect.";
        header('Location: account.php');
        exit;
    }

    // Uppdatera username, email och password om nytt lösenord angavs
    if ($newPassword) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newEmail, $newPasswordHash, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newEmail, $userId]);
    }

    // Uppdatera plattformar om något valts
    if (isset($_POST['platforms'])) {
        $stmtDelete = $pdo->prepare("DELETE FROM user_platforms WHERE user_id = ?");
        $stmtDelete->execute([$userId]);

        $stmtInsert = $pdo->prepare("INSERT INTO user_platforms (user_id, platform_id) VALUES (?, ?)");
        foreach ($_POST['platforms'] as $platformId) {
            $stmtInsert->execute([$userId, $platformId]);
        }
    }

    $_SESSION['success_message'] = "Your account has been updated successfully.";
    header('Location: account.php');
    exit;
}

// Hämta användarinformation
$stmt = $pdo->prepare("SELECT username, email, preferred_currency FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Hämta alla plattformar
$stmtPlatforms = $pdo->query("SELECT id, name FROM platforms ORDER BY name");
$allPlatforms = $stmtPlatforms->fetchAll(PDO::FETCH_ASSOC);

// Hämta användarens plattformar
$stmtUserPlatforms = $pdo->prepare("SELECT platform_id FROM user_platforms WHERE user_id = ?");
$stmtUserPlatforms->execute([$userId]);
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
            <form class="login-form" method="POST">
                <h2 class="login-form-title">Your Account</h2>

                <!-- Success/Error messages -->
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo "<p class='success-message'>{$_SESSION['success_message']}</p>";
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo "<p class='error-message'>{$_SESSION['error_message']}</p>";
                    unset($_SESSION['error_message']);
                }
                ?>

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

                <!-- Current Password -->
                <div class="login-form-group mb-4">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="login-form-control" 
                           id="current_password"
                           name="current_password" 
                           placeholder="Enter current password" required>
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
                                <input type="checkbox" name="platforms[]" value="<?= $platform['id'] ?>"
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
