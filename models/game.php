<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Game Model - FIXED VERSION
 * Simplified and debugged for your database structure
 */
class Game {
    private $conn;
    
    // Table names
    private $table_games = 'games';
    private $table_genres = 'genres';
    private $table_platforms = 'platforms';
    private $table_game_genres = 'game_genres';
    private $table_game_platforms = 'game_platforms';
    private $table_game_ratings = 'game_ratings';
    private $table_reviews = 'reviews';
    private $table_game_images = 'game_images';
    
    // Game properties
    public $id;
    public $title;
    public $slug;
    public $synopsis;
    public $developer;
    public $publisher;
    public $release_date;
    public $created_at;
    
    public function __construct() {
        try {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
            
            // Set error mode to exception for better debugging
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            error_log("Game Model Constructor Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * READ - Get all games with filters and relations
     * FIXED VERSION with better error handling
     */
    public function getAllGames($filters = [], $limit = 20, $offset = 0) {
        try {
            // Step 1: Build base query - simplified
            $query = "SELECT DISTINCT 
                        g.id, 
                        g.title, 
                        g.slug, 
                        g.synopsis, 
                        g.developer, 
                        g.publisher, 
                        g.release_date,
                        g.created_at
                    FROM {$this->table_games} g";
            
            $joins = [];
            $conditions = [];
            $params = [];
            
            // Filter by genres
            if (!empty($filters['genre_ids']) && is_array($filters['genre_ids'])) {
                $joins[] = "INNER JOIN {$this->table_game_genres} gg ON g.id = gg.game_id";
                $placeholders = [];
                foreach ($filters['genre_ids'] as $index => $genre_id) {
                    $param_name = ":genre_id_{$index}";
                    $placeholders[] = $param_name;
                    $params[$param_name] = intval($genre_id);
                }
                $conditions[] = "gg.genre_id IN (" . implode(',', $placeholders) . ")";
            }
            
            // Filter by platforms
            if (!empty($filters['platform_ids']) && is_array($filters['platform_ids'])) {
                $joins[] = "INNER JOIN {$this->table_game_platforms} gp ON g.id = gp.game_id";
                $placeholders = [];
                foreach ($filters['platform_ids'] as $index => $platform_id) {
                    $param_name = ":platform_id_{$index}";
                    $placeholders[] = $param_name;
                    $params[$param_name] = intval($platform_id);
                }
                $conditions[] = "gp.platform_id IN (" . implode(',', $placeholders) . ")";
                $conditions[] = "gp.availability_status = 'available'";
            }
            
            // Filter by minimum rating
            if (!empty($filters['min_rating'])) {
                $joins[] = "INNER JOIN {$this->table_game_ratings} gr ON g.id = gr.game_id";
                $conditions[] = "gr.avg_rating >= :min_rating";
                $params[':min_rating'] = floatval($filters['min_rating']);
            }
            
            // Search functionality
            if (!empty($filters['search'])) {
                $conditions[] = "(g.title LIKE :search 
                            OR g.synopsis LIKE :search 
                            OR g.developer LIKE :search 
                            OR g.publisher LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Combine query parts
            if (!empty($joins)) {
                $query .= " " . implode(" ", $joins);
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            // Sorting
            $sort = $filters['sort'] ?? 'newest';
            switch ($sort) {
                case 'title_asc':
                    $query .= " ORDER BY g.title ASC";
                    break;
                case 'title_desc':
                    $query .= " ORDER BY g.title DESC";
                    break;
                case 'release_newest':
                    $query .= " ORDER BY g.release_date DESC";
                    break;
                case 'release_oldest':
                    $query .= " ORDER BY g.release_date ASC";
                    break;
                case 'rating_desc':
                    // We'll sort by rating after fetching if needed
                    $query .= " ORDER BY g.created_at DESC";
                    break;
                case 'newest':
                default:
                    $query .= " ORDER BY g.created_at DESC";
                    break;
            }
            
            // Pagination
            $query .= " LIMIT :limit OFFSET :offset";
            
            // Debug: Log the query
            error_log("Game Query: " . $query);
            error_log("Params: " . print_r($params, true));
            
            // Prepare and execute
            $stmt = $this->conn->prepare($query);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', intval($limit), PDO::PARAM_INT);
            $stmt->bindValue(':offset', intval($offset), PDO::PARAM_INT);
            
            $stmt->execute();
            $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Games fetched: " . count($games));
            
            // Step 2: Enrich each game with additional data
            $enrichedGames = [];
            foreach ($games as $game) {
                // Get rating
                $rating = $this->getGameRating($game['id']);
                $game['avg_rating'] = $rating['avg_rating'];
                $game['rating_count'] = $rating['rating_count'];
                
                // Get genres
                $game['genres'] = $this->getGameGenres($game['id']);
                
                // Get platforms
                $game['platforms'] = $this->getGamePlatforms($game['id']);

                // Get Images
                $game['image'] = $this->getGameImages($game['id']);
                
                $enrichedGames[] = $game;
            }
            $games = $enrichedGames;
            
            // Sort by rating if requested
            if ($sort === 'rating_desc' && !empty($games)) {
                usort($games, function($a, $b) {
                    return $b['avg_rating'] <=> $a['avg_rating'];
                });
            }
            
            return $games;
            
        } catch (PDOException $e) {
            error_log("Error in getAllGames: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            // Return empty array instead of throwing to prevent breaking the page
            return [];
        }
    }
    
    /**
     * Get rating for a game (separate query to avoid JOIN issues)
     */
    private function getGameRating($game_id) {
        try {
            $query = "SELECT avg_rating, rating_count 
                    FROM {$this->table_game_ratings} 
                    WHERE game_id = :game_id 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'avg_rating' => floatval($result['avg_rating']),
                    'rating_count' => intval($result['rating_count'])
                ];
            }
            
            return ['avg_rating' => 0.00, 'rating_count' => 0];
            
        } catch (PDOException $e) {
            error_log("Error getting game rating: " . $e->getMessage());
            return ['avg_rating' => 0.00, 'rating_count' => 0];
        }
    }
    
    /**
     * Get single game by ID with all related data
     */
    public function getGameById($id) {
        try {
            $query = "SELECT g.*
                    FROM {$this->table_games} g
                    WHERE g.id = :id
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($game) {
                // Add rating
                $rating = $this->getGameRating($game['id']);
                $game['avg_rating'] = $rating['avg_rating'];
                $game['rating_count'] = $rating['rating_count'];
                
                // Add related data
                $game['genres'] = $this->getGameGenres($game['id']);
                $game['platforms'] = $this->getGamePlatforms($game['id']);
                $game['reviews'] = $this->getGameReviews($game['id']);
                $game['image'] = $this->getGameImages($game['id']);
                
                return $game;
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error fetching game by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get single game by slug (for SEO-friendly URLs)
     */
    public function getGameBySlug($slug) {
        try {
            $query = "SELECT g.*
                    FROM {$this->table_games} g
                    WHERE g.slug = :slug
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($game) {
                // Add rating
                $rating = $this->getGameRating($game['id']);
                $game['avg_rating'] = $rating['avg_rating'];
                $game['rating_count'] = $rating['rating_count'];
                
                // Add related data
                $game['genres'] = $this->getGameGenres($game['id']);
                $game['platforms'] = $this->getGamePlatforms($game['id']);
                $game['reviews'] = $this->getGameReviews($game['id']);
                
                return $game;
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error fetching game by slug: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all genres for a specific game
     */
    private function getGameGenres($game_id) {
        try {
            $query = "SELECT g.id, g.name
                    FROM {$this->table_genres} g
                    INNER JOIN {$this->table_game_genres} gg ON g.id = gg.genre_id
                    WHERE gg.game_id = :game_id
                    ORDER BY g.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching game genres: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all images for a specific game
     */

    private function getGameImages($game_id) {
        try {
            $query = "SELECT
                        gi.url
                    FROM {$this->table_game_images} gi
                    WHERE gi.game_id = :game_id
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['url'] ;
            
        } catch (PDOException $e) {
            error_log("Error fetching game images: " . $e->getMessage());
            return "error";
        }
    }
    
    /**
     * Get all platforms for a specific game with pricing
     */
    private function getGamePlatforms($game_id) {
        try {
            $query = "SELECT 
                        p.id, 
                        p.name, 
                        p.platform_type,
                        gp.availability_status,
                        gp.price,
                        gp.currency,
                        gp.region,
                        gp.url
                      FROM {$this->table_platforms} p
                      INNER JOIN {$this->table_game_platforms} gp ON p.id = gp.platform_id
                      WHERE gp.game_id = :game_id
                      ORDER BY p.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching game platforms: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get reviews for a specific game
     */
    private function getGameReviews($game_id, $limit = 5) {
        try {
            $query = "SELECT 
                        r.id,
                        r.rating,
                        r.title,
                        r.body,
                        r.created_at,
                        r.verified,
                        u.username
                      FROM {$this->table_reviews} r
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.game_id = :game_id
                      ORDER BY r.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':game_id', $game_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching game reviews: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of games (for pagination)
     */
    public function getTotalCount($filters = []) {
        try {
            $query = "SELECT COUNT(DISTINCT g.id) as total
                      FROM {$this->table_games} g";
            
            $joins = [];
            $conditions = [];
            $params = [];
            
            // Apply same filters as getAllGames
            if (!empty($filters['genre_ids']) && is_array($filters['genre_ids'])) {
                $joins[] = "INNER JOIN {$this->table_game_genres} gg ON g.id = gg.game_id";
                $placeholders = [];
                foreach ($filters['genre_ids'] as $index => $genre_id) {
                    $param_name = ":genre_id_{$index}";
                    $placeholders[] = $param_name;
                    $params[$param_name] = intval($genre_id);
                }
                $conditions[] = "gg.genre_id IN (" . implode(',', $placeholders) . ")";
            }
            
            if (!empty($filters['platform_ids']) && is_array($filters['platform_ids'])) {
                $joins[] = "INNER JOIN {$this->table_game_platforms} gp ON g.id = gp.game_id";
                $placeholders = [];
                foreach ($filters['platform_ids'] as $index => $platform_id) {
                    $param_name = ":platform_id_{$index}";
                    $placeholders[] = $param_name;
                    $params[$param_name] = intval($platform_id);
                }
                $conditions[] = "gp.platform_id IN (" . implode(',', $placeholders) . ")";
                $conditions[] = "gp.availability_status = 'available'";
            }
            
            if (!empty($filters['min_rating'])) {
                $joins[] = "INNER JOIN {$this->table_game_ratings} gr ON g.id = gr.game_id";
                $conditions[] = "gr.avg_rating >= :min_rating";
                $params[':min_rating'] = floatval($filters['min_rating']);
            }
            
            if (!empty($filters['search'])) {
                $conditions[] = "(g.title LIKE :search 
                               OR g.synopsis LIKE :search 
                               OR g.developer LIKE :search 
                               OR g.publisher LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($joins)) {
                $query .= " " . implode(" ", $joins);
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Error getting total count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * CREATE - Insert new game with genres and platforms
     */
    public function create($genres = [], $platforms = []) {
        try {
            $this->conn->beginTransaction();
            
            // Insert game
            $query = "INSERT INTO {$this->table_games} 
                      (title, slug, synopsis, developer, publisher, release_date) 
                      VALUES (:title, :slug, :synopsis, :developer, :publisher, :release_date)";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize
            $this->title = htmlspecialchars(strip_tags($this->title));
            $this->slug = htmlspecialchars(strip_tags($this->slug));
            $this->synopsis = htmlspecialchars(strip_tags($this->synopsis));
            $this->developer = htmlspecialchars(strip_tags($this->developer));
            $this->publisher = htmlspecialchars(strip_tags($this->publisher));
            
            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':slug', $this->slug);
            $stmt->bindParam(':synopsis', $this->synopsis);
            $stmt->bindParam(':developer', $this->developer);
            $stmt->bindParam(':publisher', $this->publisher);
            $stmt->bindParam(':release_date', $this->release_date);
            
            $stmt->execute();
            $this->id = $this->conn->lastInsertId();
            
            // Insert genres
            if (!empty($genres)) {
                $this->addGenres($this->id, $genres);
            }
            
            // Insert platforms
            if (!empty($platforms)) {
                $this->addPlatforms($this->id, $platforms);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating game: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * UPDATE - Update game and relations
     */
    public function update($genres = null, $platforms = null) {
        try {
            $this->conn->beginTransaction();
            
            // Update game
            $query = "UPDATE {$this->table_games} 
                      SET title = :title,
                          slug = :slug,
                          synopsis = :synopsis,
                          developer = :developer,
                          publisher = :publisher,
                          release_date = :release_date
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize
            $this->title = htmlspecialchars(strip_tags($this->title));
            $this->slug = htmlspecialchars(strip_tags($this->slug));
            $this->synopsis = htmlspecialchars(strip_tags($this->synopsis));
            $this->developer = htmlspecialchars(strip_tags($this->developer));
            $this->publisher = htmlspecialchars(strip_tags($this->publisher));
            
            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':slug', $this->slug);
            $stmt->bindParam(':synopsis', $this->synopsis);
            $stmt->bindParam(':developer', $this->developer);
            $stmt->bindParam(':publisher', $this->publisher);
            $stmt->bindParam(':release_date', $this->release_date);
            $stmt->bindParam(':id', $this->id);
            
            $stmt->execute();
            
            // Update genres if provided
            if ($genres !== null) {
                $this->removeAllGenres($this->id);
                $this->addGenres($this->id, $genres);
            }
            
            // Update platforms if provided
            if ($platforms !== null) {
                $this->removeAllPlatforms($this->id);
                $this->addPlatforms($this->id, $platforms);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating game: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * DELETE - Remove game (cascade deletes handled by foreign keys)
     */
    public function delete() {
        try {
            $query = "DELETE FROM {$this->table_games} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting game: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Helper: Add genres to game
     */
    private function addGenres($game_id, $genre_ids) {
        $query = "INSERT INTO {$this->table_game_genres} (game_id, genre_id) VALUES (:game_id, :genre_id)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($genre_ids as $genre_id) {
            $stmt->execute([
                ':game_id' => $game_id,
                ':genre_id' => $genre_id
            ]);
        }
    }
    
    /**
     * Helper: Add platforms to game
     */
    private function addPlatforms($game_id, $platforms) {
        $query = "INSERT INTO {$this->table_game_platforms} 
                (game_id, platform_id, availability_status, price, currency, region, url) 
                VALUES (:game_id, :platform_id, :availability_status, :price, :currency, :region, :url)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($platforms as $platform) {
            $stmt->execute([
                ':game_id' => $game_id,
                ':platform_id' => $platform['platform_id'],
                ':availability_status' => $platform['availability_status'] ?? 'available',
                ':price' => $platform['price'] ?? null,
                ':currency' => $platform['currency'] ?? null,
                ':region' => $platform['region'] ?? null,
                ':url' => $platform['url'] ?? null
            ]);
        }
    }
    
    /**
     * Helper: Remove all genres from game
     */
    private function removeAllGenres($game_id) {
        $query = "DELETE FROM {$this->table_game_genres} WHERE game_id = :game_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':game_id' => $game_id]);
    }
    
    /**
     * Helper: Remove all platforms from game
     */
    private function removeAllPlatforms($game_id) {
        $query = "DELETE FROM {$this->table_game_platforms} WHERE game_id = :game_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':game_id' => $game_id]);
    }
}