<!DOCTYPE html>
<?php
    // This top part will handle AJAX requests
    if (!empty($_GET['ajax'])) {
        // Simulate fetching and displaying games for the AJAX response.
        include_once __DIR__ . '/components/game_list.php';
        exit; // Stop further execution for AJAX requests
    }
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/components/head.php'; ?>
</head>
<body>
    <?php
    // Tell the header which page is active (optional)
    $activePage = 'home';
    // Optionally set a custom site title for this page
    // $siteTitle = 'GameDex - Home';
    include_once __DIR__ . '/components/header.php';
    ?>
    <main>
        <h1>Game Catalog</h1>
        <?php
            // Include filter box
            include_once __DIR__ . '/components/filterbox.php';
        ?>
        <div id="game-catalog-container">
            <?php include_once __DIR__ . '/components/game_list.php'; // Initial game list load ?>
        </div>
    </main>
</body>
</html>