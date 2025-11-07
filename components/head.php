<?php
// If the page wants a custom title, set it before including head.php
$siteTitle = $siteTitle ?? 'GameDex';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $siteTitle ?></title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="assests/GameDex_logo.png">

<!-- Styles -->
<link rel="stylesheet" href="styles/styles.css?v=<?= filemtime('styles/styles.css') ?>">

<!-- Font Awesome Icons -->
<script src="https://kit.fontawesome.com/cffa9d18cb.js" crossorigin="anonymous"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
