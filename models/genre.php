<?php

require_once __DIR__ . '/../config/database.php';

// Genre Model Class
// Handles genre-related database operations

class Genre{
    private $conn;
    private $table = 'genres';
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Get all genres ordered alphabetically
     */
    public function getAll() {
        $query = "SELECT id, name FROM {$this->table} ORDER BY name ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching genres: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get genres with game count (useful for showing popular genres)
     */
    public function getAllWithCount() {
        $query = "SELECT g.id, g.name,
                    COUNT(gg.game_id) as game_count
                FROM {$this->table} g
                LEFT JOIN game_genres gg ON g.id = gg.genre_id
                GROUP BY g.id, g.name
                HAVING game_count > 0
                ORDER BY g.name ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching genres with count: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single genre by ID
     */
    public function getById($id) {
        $query = "SELECT id, name FROM {$this->table} WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching genre: " . $e->getMessage());
            return null;
        }
    }
}
