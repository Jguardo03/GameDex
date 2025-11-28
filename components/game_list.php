<?php
// This file is responsible for fetching and displaying the list of games.
// It can be included on initial page load and also called via AJAX.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/game.php';
$pdo = DataBase::getInstance()->getConnection();

// Receive filter parameters from GET request
$selectedGenre = $_GET['genre'] ?? '';
$selectedPlatform = $_GET['platform'] ?? '';
$userId = $_SESSION['user_id']?? null;
//Verify user is sign In
if(!isset($userId)){
    $userStatus = "Logout";
}


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
        $gameId = $game['id'];
        $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$userId, $gameId]);
        echo '<div class="game-card">';
        echo '<div class="game-card-image">';
        if($stmt->fetch()){
            echo '<i class="fa-solid fa-heart-circle-minus fa-2xl icon-heart" data-game-id='. $game["id"].'></i>';
        }else{
            echo '<i class="fa-regular fa-heart fa-2xl icon-heart"  data-game-id='. $game["id"].';></i>';
        }
        echo '<img src="./assests/GameDex_logo.png" alt="Game Image">';
        echo '</div>';
        echo '<div class="game-card-content" data-game-id='. $game["id"].'>';
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

<script>
    function attachHearthClick(){
        
        const heartIcon = $('.icon-heart');
        heartIcon.off('click').on('click', function() {
            let userStatus = <?php echo json_encode('$userStatus');?>;
            if(userStatus=="Logout"){
            console.log("Login to add games to your wishlist") 
            alert("Login to add games to your wishlist");
            return;
            }
            const gameId = $(this).data('game-id');
            const icon =$(this);
            console.log(gameId);
            $.post('components/addToWishlist.php',{game_id: gameId},function(response){
                console.log(response);
                if(response == 'Added'){
                    icon.removeClass('fa-regular fa-heart').addClass('fa-solid fa-heart-circle-minus');
                    return;
                } else if(response == 'removed'){
                    icon.removeClass('fa-solid fa-heart-circle-minus').addClass('fa-regular fa-heart');
                }
            });
        });
    }

    function onGameClick(){
        $('.game-card-content').off('click').on('click', function() {
            const gameId = $(this).data('game-id');
            window.location.href = 'gameDetails.php?game_id=' + gameId;
        });
    }

    $(document).ready(function() {
        attachHearthClick();
        onGameClick();
    });
</script>
        