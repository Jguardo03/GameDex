<?php
// Page-specific settings
$siteTitle = 'GameDex - Sign Up';
$activePage = 'Account';

session_start();
require_once __DIR__ . '/config/database.php';

$pdo = DataBase::getInstance()->getConnection();
$error = '';

// email validation
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// password min char req
function isStrongPassword($password) {
    return strlen(trim($password)) >= 6;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $preferred_currency = $_POST['preferred_currency'] ?? 'AUD';
    $platforms = $_POST['platforms'] ?? [];

    // Reset error
    $error = '';

    // validate fields
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!"; // no blank txt fields
    } elseif (!isValidEmail($email)) {
        $error = "Invalid email format!";
    }

    // create user if no error
    if ($error === '') {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "Username or email already taken!";
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, preferred_currency) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash, $preferred_currency]);
                $user_id = $pdo->lastInsertId();

                // Optional: insert platforms if selected
                if ($platforms) {
                    $stmtPlatform = $pdo->prepare("SELECT id FROM platforms WHERE name = ?");
                    $stmtInsertPlatform = $pdo->prepare("INSERT INTO user_platforms (user_id, platform_id) VALUES (?, ?)");
                    foreach ($platforms as $pname) {
                        $stmtPlatform->execute([$pname]);
                        $row = $stmtPlatform->fetch(PDO::FETCH_ASSOC);
                        if ($row) {
                            $stmtInsertPlatform->execute([$user_id, $row['id']]);
                        }
                    }
                }

                // Set success message and redirect to login
                $_SESSION['success_message'] = "Account successfully created!"; // → meddelande till användare
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error during signup: " . $e->getMessage());
            $error = "Something went wrong. Please try again later."; 
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/components/head.php'; ?>
    <link rel="stylesheet" href="styles/signup_styles.css?v=<?= filemtime('styles/signup_styles.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?> 

    <main>
        <div class="login-page-container">
            <form class="login-form" method="POST">
                <h2 class="login-form-title">Sign Up</h2>

                <?php if($error) echo "<p class='error-message'>$error</p>"; ?>

                <div class="login-form-group mb-4">
                    <input type="text" class="login-form-control" name="username" placeholder="Username" required>
                </div>

                <div class="login-form-group mb-4">
                    <input type="email" class="login-form-control" name="email" placeholder="Email" required>
                </div>

                <div class="login-form-group mb-4">
                    <input type="password" class="login-form-control" name="password" placeholder="Password" required>
                </div>

                <div class="login-form-group mb-4">
                    <select class="login-form-control" name="preferred_currency" required>
                        <option value="AUD" selected>AUD</option>
                    </select>
                </div>

                <!-- Platforms checkboxes (optional) -->
                <div class="login-form-group mb-4">
                    <label>Owned consoles and platforms:</label>
                    <div>
                        <label><input type="checkbox" name="platforms[]" value="PC"> PC</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Mac"> Mac</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Linux"> Linux</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Steam Deck"> Steam Deck</label><br>
                        <label><input type="checkbox" name="platforms[]" value="PlayStation 5"> PlayStation 5</label><br>
                        <label><input type="checkbox" name="platforms[]" value="PlayStation 4"> PlayStation 4</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Xbox Series X"> Xbox Series X</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Xbox Series S"> Xbox Series S</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Xbox One"> Xbox One</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Nintendo Switch"> Nintendo Switch</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Cloud"> Cloud</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Mobile iOS"> Mobile iOS</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Mobile Android"> Mobile Android</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Steam"> Steam</label><br>
                        <label><input type="checkbox" name="platforms[]" value="Epic Games Store"> Epic Games Store</label><br>
                        <label><input type="checkbox" name="platforms[]" value="GOG"> GOG</label><br>
                    </div>
                </div>

                <button type="submit" class="login-btn-primary mb-3">Sign Up</button>

                <div class="text-center mb-3">
                    <span>Already have an account?</span>
                    <a href="login.php" class="login-link" style="font-weight: bold;">Login</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
