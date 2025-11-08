<?php

include_once __DIR__ .'/../models/wishlist.php';
include_once __DIR__ .'/../models/game.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$wishlistModule = new Wishlist();
$pdo = DataBase::getInstance()->getConnection();
$userId = $_SESSION['user_id']?? null;
//Verify user is sign In
if(!isset($userId)){
    echo "<script>alert('Please Sign In to view your Wishlist');</script>";
    exit;
}
//Fetch Games 
$games = $wishlistModule->getAllWishlist($_SESSION['user_id']);

//Display Games

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

<script>
    function attachHearthClick(){
        const heartIcon = $('.icon-heart');
        heartIcon.off('click').on('click', function() {
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

    $(document).ready(function() {
        attachHearthClick();
    });

    $.ajax({
                url: 'components/wishlist_list.php', // Corrected URL
                type: 'GET', // Changed to GET for simplicity and bookmarking
                data:{ajax: 1 // Flag to identify AJAX request
                },
                success:function(response){
                    // Replace the content of the game-catalog-container with the response
                    $('#wishlist-container').html(response);
                    attachHearthClick();
                },
                error:function(xhr,status,error){
                    console.error("AJAX Error: " + status + " - " + error);
                }
            });
</script>