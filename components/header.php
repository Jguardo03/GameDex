<?php
// Reusable header component
// Usage (in any page):
// $siteTitle = 'GameDex'; // optional
// $activePage = 'home'; // optional: 'home', 'games', 'about', etc.
// include __DIR__ . '/header.php';

// Default values (if not set by the including page)
$siteTitle = isset($siteTitle) ? $siteTitle : 'GameDex';
$activePage = isset($activePage) ? $activePage : '';
?>

<header class="site-header">
    <div class="header-container">
        <div class="brand">
            <img src="./assests/GameDex_logo.png" alt="GameDex Logo" class="logo">
        </div>
        <nav class="main-nav" >
            <ul>
                <div class="icon">
                    <i class="fa-regular fa-house"></i>
                    <li class="<?= ($activePage === 'home') ? 'active' : '' ?>"><a href="index.php">Home</a></li>
                </div>
                <div class="icon">
                    <i class="fa-regular fa-heart"></i>
                    <li class="<?= ($activePage === 'Whishlist') ? 'active' : '' ?>"><a href="">Whishlist</a></li>
                </div>
                <div  class="icon">
                    <i class="fa-regular fa-user"></i>
                    <li class="<?= ($activePage === 'Account') ? 'active' : '' ?>"><a href="account.php">Account</a></li>
                </div>
            </ul>
        </nav>
    </div>
</header>
