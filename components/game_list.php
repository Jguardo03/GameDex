<?php
// This file is responsible for fetching and displaying the list of games.
// It can be included on initial page load and also called via AJAX.

require_once __DIR__ . '/../models/game.php';

// Receive filter parameters from GET request
$selectedGenre = $_GET['genre'] ?? '';
$selectedPlatform = $_GET['platform'] ?? '';

$gameModel = new Game();

$gameModel = new Game();
if(!empty($selectedGenre) && !empty($selectedPlatform)) {
    // Fetch games based on filters
    $filters =['platform_ids' => [$selectedPlatform], 'genre_ids' => [$selectedGenre]];
    $games = $gameModel->getAllGames($filters);
} elseif(empty($selectedGenre) && !empty($selectedPlatform)){
    $filters =['platform_ids' => [$selectedPlatform]];
    $games = $gameModel->getAllGames($filters);
} elseif(!empty($selectedGenre) && empty($selectedPlatform)){
    $filters =['genre_ids' => [$selectedGenre]];
    $games = $gameModel->getAllGames($filters);
}else {
    // Fetch all games if no filters are applied
    $games = $gameModel->getAllGames();
}
// Display the games
if (empty($games)) {
    echo '<p>No games found matching the selected filters.</p>';
} else {
    echo '<div class="card-grid-game">';
    foreach($games as $game){
        echo '<div class="game-card">';
        echo '<div class="game-card-image">';
        echo '<i class="fa-regular fa-heart fa-2xl icon-heart"></i>';
        echo '<img src="./assests/GameDex_logo.png" alt="Game Image">';
        echo '</div>';
        echo '<div class="game-card-content">';
        echo '<h3>' . htmlspecialchars($game['title']) . '</h3>';
        echo '<div class="game-card-elements">';
        foreach($game['platforms'] as $platform){
            echo '<span class="badge badge-platform">' . htmlspecialchars($platform['name']) . '</span>';
        }
        echo '</div>';
        echo '<div class="game-card-elements">';
        foreach($game['genres'] as $genre){
            echo '<span class="badge badge-genre">' . htmlspecialchars($genre['name']) . '</span>';
        }
        echo '</div>';
        echo '<spam class ="badge rating">' .htmlspecialchars($game['avg_rating'],2) . '⭐</span>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}
?>