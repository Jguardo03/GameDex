<?php

session_start();

require_once 'config/database.php';

$gameId = $_POST['game_id'] ?? null;
$title = htmlspecialchars(strip_tags($_POST['title']?? ''));
$rating = $_POST['raiting'] ?? null;
$body = htmlspecialchars(strip_tags($_POST['body']?? ''));
$userId = $_SESSION['user_id'] ?? null;

//validate user sign in
if(!isset($userId)){
    echo json_encode(['status' => 'error', 'message' => 'User not signed in.']);
    exit;
}

//validate input

if (!$gameId || !$title || !$rating || !$body) {
    echo $gameId;
    echo $title;
    echo $rating;
    echo $body;

    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

$pdo = DataBase::getInstance()->getConnection();

try{

    $querry = "INSERT INTO reviews (game_id, user_id,rating, title, body, created_at) VALUES (:game_id, :user_id, :rating, :title, :body, NOW())";

    $stmt = $pdo->prepare($querry);

    //Bind parameters (prevent SQL injection)

    $stmt->bindParam(':game_id', $gameId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':body', $body, PDO::PARAM_STR);

    $stmt->execute();

    //success response

    header('Location: gameDetails.php?game_id=' . $gameId. '&review_submitted=1');
    exit;
}catch (PDOException $e){
    error_log("error submitting review: " . $e->getMessage());
    die("Error submitting review. Please try again");
}