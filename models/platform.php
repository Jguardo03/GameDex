<?php

require_once __DIR__ . '/../config/database.php';

// Platform Model Class
// Handles platform-related database operations

class Platform {
    private $conn;
    private $table = 'platforms';
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Get all platforms ordered by name
     */
    public function getAll() {
        $query = "SELECT id, name, platform_type FROM {$this->table} ORDER BY name ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching platforms: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get platforms grouped by type (for organized filter UI)
     */
    public function getAllGroupedByType() {
        $query = "SELECT id, name, platform_type FROM {$this->table} ORDER BY platform_type, name ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $platforms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by platform_type
            $grouped = [];
            foreach ($platforms as $platform) {
                $type = $platform['platform_type'] ?? 'Other';
                if (!isset($grouped[$type])) {
                    $grouped[$type] = [];
                }
                $grouped[$type][] = $platform;
            }
            
            return $grouped;
        } catch (PDOException $e) {
            error_log("Error fetching grouped platforms: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get platforms with game count
     */
    public function getAllWithCount() {
        $query = "SELECT p.id, p.name, p.platform_type,
                    COUNT(DISTINCT gp.game_id) as game_count
                FROM {$this->table} p
                LEFT JOIN game_platforms gp ON p.id = gp.platform_id
                WHERE gp.availability_status = 'available'
                GROUP BY p.id, p.name, p.platform_type
                HAVING game_count > 0
                ORDER BY p.name ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching platforms with count: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single platform by ID
     */
    public function getById($id) {
        $query = "SELECT id, name, platform_type FROM {$this->table} WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching platform: " . $e->getMessage());
            return null;
        }
    }
}