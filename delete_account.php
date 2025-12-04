<?php
session_start();
require_once __DIR__ . "/config/database.php";
$pdo = DataBase::getInstance()->getConnection();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        // Delete user from database
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        // Set success message
        $_SESSION['success_message'] = "Your account has been deleted successfully.";

        // Remove login-related session variables
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);

        // Redirect to login page
        header('Location: login.php');
        exit;
    } elseif (isset($_POST['cancel_delete'])) {
        // Redirect back to account page if user cancels
        header('Location: account.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/components/head.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?>

    <main>
        <h1>Delete Account</h1>
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>

        <form method="POST">
            <button type="submit" name="confirm_delete">Yes, delete my account</button>
            <button type="submit" name="cancel_delete">No, keep my account</button>
        </form>
    </main>
</body>
</html>
