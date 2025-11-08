<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/game.php";

// Wishlist Model Class
// Handles wishlist-related database operations


class Wishlist{
    //wishlist properties
    public $id;
    public $user_id;
    public $game_id;
    

    public function __construct() {
        try {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
            
            // Set error mode to exception for better debugging
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            error_log("Wishlist Model Constructor Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllWishlist($user_id){
        $query = "SELECT * FROM wishlist WHERE user_id = :user_id";
        $gameModel = new Game($this->conn);

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $wishlist= $stmt->fetchAll(PDO::FETCH_ASSOC);
            $games=[];
            foreach($wishlist as $item){
                $game = $gameModel->getGameById($item['game_id']);
                if($game){
                    $games[]=$game;
                }
            }

            return $games;
        } catch (PDOException $e) {
            error_log("Error fetching wishlist: " . $e->getMessage());
            return [];
        }
    }
}
