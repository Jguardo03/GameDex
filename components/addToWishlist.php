<?php 

session_start();

require_once __DIR__ . '/../config/database.php';
$pdo = DataBase::getInstance()->getConnection();

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

if(!isset($_POST['game_id'])){
    echo "No Game iD";
    exit;
}
$userId = $_SESSION['user_id'];
$gameId = $_POST['game_id'];

$stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?");
$stmt ->execute([$userId , $gameId]);
if($stmt->fetch()){
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$userId, $gameId]);
    echo 'removed';
    exit;
}else{
    $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, game_id) VALUES (?, ?)");
    $stmt->execute([$userId, $gameId]);
    $id = $pdo->lastInsertId();
    echo 'Added';
    exit;
}


