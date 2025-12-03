<?php


require_once 'models/Game.php';

$game_id = $_GET['game_id'];

$activePage = 'game_details';
if (!isset($game_id)) {
    echo "Game ID is missing.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/components/head.php'; ?>
</head>
<body>
    <?php
    
    include_once __DIR__ . '/components/header.php';

    $gameModel = new Game();
    $game = $gameModel->getGameById($game_id);
    $userId = $_SESSION['user_id']?? null;
    $pdo = DataBase::getInstance()->getConnection();
    if(!isset($userId)){
    $userStatus = "Logout";
    }
    echo '<main>';
    echo '<div style="margin:10px; color:#1798D8; font-weight:bold;">';
    echo '<i class="fa-solid fa-chevron-left"></i>';
    echo '<a href="index.php" style="color: #1798D8">Back to Catalog</a>';
    echo '</div>';
    echo '<section class="card">';
    echo '<section class="game-image">';
    $image = $game['image'];
    echo '<img src='.htmlspecialchars($image).' alt="Game Image">';
    echo '<div class="game-header-details">';
    echo '<h1 style="color: white">' . htmlspecialchars($game['title']) . '</h1>';
    echo '<div class="game-header-elements">';
    echo '<spam class ="badge rating">' .htmlspecialchars($game['avg_rating'],2) . '⭐</span>';
    foreach($game['genres'] as $genre){
        echo '<span class="badge badge-genre">' . htmlspecialchars($genre['name']) . '</span>';
    }
    foreach($game['platforms'] as $platform){
        echo '<span class="badge badge-platform">' . htmlspecialchars($platform['name']) . '</span>';
    }
    echo '</div>';
    echo '</div>';
    echo '</section>';
    echo '<article class="game-description">';
    echo '<div class="text-button">';
    echo '<div class="publisher-release">';
    echo '<i class="fa-regular fa-calendar fa-lg"></i>';
    echo '<p>' . htmlspecialchars($game['release_date']) . '</p>';
    echo '<i class="fa-regular fa-building fa-lg"></i>';
    echo '<p>' . htmlspecialchars($game['publisher']) . '</p>';
    echo '</div>';
    echo '<div class=button>';
    $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$userId, $game['id']]);
    if($stmt->fetch()){
        echo '<i class="fa-solid fa-heart-circle-minus fa-lg icon-heart" data-game-id='. $game["id"].'></i>';
    }else{
        echo '<i class="fa-regular fa-heart fa-lg icon-heart"  data-game-id='. $game["id"].';></i>';
    }
    echo '<p>Add to Wishlist</p>';
    echo '</div>';
    echo '</div>';
    echo '<h3>About this Game</h3>';
    echo '<p>' . htmlspecialchars($game['synopsis']) . '</p>';
    echo '</article>';
    echo '</section>';
    echo '<h3 style="margin:20px;">Reviews ('. count($game['reviews']). ') </h3>';
    echo '<section class=card>';
    echo '<h3> Write a Review</h3>';
    echo '<form id="review-form" method="POST" action="submit_review.php">';
    echo '<input type="hidden" name="game_id" value="'. htmlspecialchars($game['id']) .'">';
    echo '<label for="title">Title:</label>';
    echo '<input type="text" id="title" name="title" required>';
    echo '<label for="rating">Your Rating:</label>';
    echo '<select id="rating" name="raiting" value=option required>';
    for ($i = 1; $i <= 5; $i++) {
        echo '<option value="' . $i . '">' . $i . '⭐</option>';
    }
    echo '</select>';
    echo '<label for="body">Your Review:</label>';
    echo '<textarea id="body" name="body" rows="4" required></textarea>';
    echo '<button type="submit" class=button >Submit Review</button>';
    echo '</form>';
    echo '</section>';
    foreach($game['reviews'] as $review){
        echo '<section class="card" style="margin-top:20px;">';
        echo '<div class="review-header">';
        echo '<div>';
        echo '<h3 style="margin:4px">' . htmlspecialchars($review['username']) . '</h3>';
        echo '<label>' . htmlspecialchars($review['created_at']) . '</label>';
        echo '</div>';
        echo '<span class ="badge rating">' .htmlspecialchars($review['rating'],2) . '⭐</span>';
        echo '</div>';
        echo '<h4>' . htmlspecialchars($review['title']) . '</h4>';
        echo '<p>' . htmlspecialchars($review['body']) . '</p>';
        echo '</section>';
    }
    echo '</main>';
    echo '<div id="review-response"></div>';
    ?>
</body>
<script>
    document.getEelementById('review-form').addEventListener('submit', async function(e)  {
        e.preventDefault()// Prevent the default form submission

        const formData = new FormData(this);
        const messageDiv = document.getElementById('review-response');

        try{
            const response = await fetch('submit_review.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                messageDiv.innerHTML = '<p style="color:green;">' + result.message + '</p>';
                // Optionally, you can reset the form here
                this.reset();
            } else {
                messageDiv.innerHTML = '<p style="color:red;">' + result.message + '</p>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<p style="color:red;">An error occurred while submitting your review. Please try again later.</p>';
        }
    });
    