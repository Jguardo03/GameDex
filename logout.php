<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Start a new session just to carry the notification
session_start();
$_SESSION['success_message'] = "Successfully logged out.";

// Redirect to login page
header('Location: login.php');
exit;
