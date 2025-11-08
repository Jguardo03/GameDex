<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Reusable header component
// Default values (if not set by the including page)
$siteTitle = isset($siteTitle) ? $siteTitle : 'GameDex';
$activePage = isset($activePage) ? $activePage : '';

// Start session om den inte redan är startad

// Bestäm Account-länk
$accountLink = isset($_SESSION['user_id']) ? 'account.php' : 'login.php';
?>

<header class="site-header">
    <div class="header-container">
        <div class="brand">
            <img src="./assests/GameDex_logo.png" alt="GameDex Logo" class="logo">
        </div>
        <nav class="main-nav">
            <ul class ="nav-list">
                <div class="icon">
                    <i class="fa-regular fa-house"></i>
                    <li class="<?= ($activePage === 'home') ? 'active' : '' ?>"><a href="index.php">Home</a></li>
                </div>
                <div class="icon">
                    <i class="fa-regular fa-heart"></i>
                    <li class="<?= ($activePage === 'Whishlist') ? 'active' : '' ?>"><a href="wishlist.php">Wishlist</a></li>
                </div>
                <div class="icon">
                    <i class="fa-regular fa-user"></i>
                    <li class="<?= ($activePage === 'Account') ? 'active' : '' ?>"><a href="<?= $accountLink ?>">Account</a></li>
                </div>
            </ul>
            
        </nav>
    </div>
</header>
