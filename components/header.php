<?php
// Reusable header component
// Usage (in any page):
// $siteTitle = 'GameDex'; // optional
// $activePage = 'home'; // optional: 'home', 'games', 'about', etc.
// include __DIR__ . '/header.php';

// Default values (if not set by the including page)
$siteTitle = isset($siteTitle) ? $siteTitle : 'GameDex';
$activePage = isset($activePage) ? $activePage : '';

// Optional: path to CSS file. Pages can override by setting $cssPath before including this file.
$cssPath = isset($cssPath) ? $cssPath : './styles/styles.css';
// Avoid printing the <link> multiple times if header is included more than once
if (!isset($GLOBALS['gamedex_header_css_included'])) {
    echo '<link rel="stylesheet" href="' . htmlspecialchars($cssPath, ENT_QUOTES, 'UTF-8') . '">';
    $GLOBALS['gamedex_header_css_included'] = true;
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://kit.fontawesome.com/cffa9d18cb.js" crossorigin="anonymous"></script>
</head>

<header class="site-header">
    <div class="header-container">
        <div class="brand">
            <img src="./assests/GameDex_logo.png" alt="GameDex Logo" class="logo">
        </div>
        <nav class="main-nav" >
            <ul>
                <div class="icon">
                    <i class="fa-regular fa-house"></i>
                    <li class="<?= ($activePage === 'home') ? 'active' : '' ?>"><a href="/index.php">Home</a></li>
                </div>
                <div class="icon">
                    <i class="fa-regular fa-heart"></i>
                    <li class="<?= ($activePage === 'Whishlist') ? 'active' : '' ?>"><a href="/games.php">Whishlist</a></li>
                </div>
                <div  class="icon">
                    <i class="fa-regular fa-user"></i>
                    <li class="<?= ($activePage === 'Account') ? 'active' : '' ?>"><a href="/about.php">Account</a></li>
                </div>
            </ul>
        </nav>
    </div>
</header>
