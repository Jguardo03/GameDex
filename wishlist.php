<?php 

$siteTitle = 'GameDex - Wishlist';
$activePage = 'Wishlist';

require_once __DIR__ . "/config/database.php";
$pdo = DataBase::getInstance()->getConnection();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/components/head.php'; ?>
</head>
<body>
    <?php
    // Tell the header which page is active (optional)
    // Optionally set a custom site title for this page
    // $siteTitle = 'GameDex - Home';
    include_once __DIR__ . '/components/header.php';
    ?>
    <main>
        <h1>WishList Games</h1>
        <div id="wishlist-container">
            <?php
            //load wishlist games
            include_once __DIR__ . '/components/wishlist_list.php';
        ?>
        </div>
    </main>
</body>
</html>