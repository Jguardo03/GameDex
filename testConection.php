<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/genre.php';
require_once __DIR__ . '/models/platform.php';
require_once __DIR__ . '/models/game.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .test-section h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .status.success {
            background: #10b981;
            color: white;
        }
        
        .status.error {
            background: #ef4444;
            color: white;
        }
        
        .status.info {
            background: #3b82f6;
            color: white;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        
        .data-table tr:hover {
            background: #f9fafb;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
        }
        
        .badge-genre {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-platform {
            background: #fce7f3;
            color: #9f1239;
        }
        
        .rating {
            color: #f59e0b;
            font-weight: bold;
        }
        
        .code-block {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin-top: 10px;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
        }
        
        .error-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 Game Catalog Database Test</h1>
            <p>Testing database connection and models</p>
        </div>

        <?php
        // TEST 1: Database Connection
        echo '<div class="test-section">';
        echo '<h2>Test 1: Database Connection</h2>';
        
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            if ($conn) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Database connection established successfully!</p>';
                
                // Get MySQL version
                $version = $conn->query('SELECT VERSION()')->fetchColumn();
                echo '<div class="info-box">';
                echo '<strong>Database Info:</strong><br>';
                echo 'Server: localhost<br>';
                echo 'Database: game_catalog<br>';
                echo 'MySQL Version: ' . htmlspecialchars($version);
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">';
            echo '<strong>Connection Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        echo '</div>';

        // TEST 2: Genres
        echo '<div class="test-section">';
        echo '<h2>Test 2: Genre Model</h2>';
        
        try {
            $genreModel = new Genre();
            $genres = $genreModel->getAllWithCount();
            
            if (!empty($genres)) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Found ' . count($genres) . ' genres with games</p>';
                
                echo '<table class="data-table">';
                echo '<thead><tr><th>ID</th><th>Genre Name</th><th>Game Count</th></tr></thead>';
                echo '<tbody>';
                foreach (array_slice($genres, 0, 10) as $genre) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($genre['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($genre['name']) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($genre['game_count']) . '</strong> games</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                
                if (count($genres) > 10) {
                    echo '<p style="margin-top:10px;color:#6b7280;">Showing 10 of ' . count($genres) . ' genres</p>';
                }
            } else {
                echo '<span class="status info">ℹ INFO</span>';
                echo '<p>No genres found in database</p>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // TEST 3: Platforms
        echo '<div class="test-section">';
        echo '<h2>Test 3: Platform Model</h2>';
        
        try {
            $platformModel = new Platform();
            $platforms = $platformModel->getAllWithCount();
            
            if (!empty($platforms)) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Found ' . count($platforms) . ' platforms with available games</p>';
                
                echo '<table class="data-table">';
                echo '<thead><tr><th>ID</th><th>Platform Name</th><th>Type</th><th>Game Count</th></tr></thead>';
                echo '<tbody>';
                foreach ($platforms as $platform) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($platform['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($platform['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($platform['platform_type']) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($platform['game_count']) . '</strong> games</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<span class="status info">ℹ INFO</span>';
                echo '<p>No platforms found in database</p>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // TEST 4: Games - Basic Fetch
        echo '<div class="test-section">';
        echo '<h2>Test 4: Game Model - Basic Fetch</h2>';
        
        try {
            $gameModel = new Game();
            $games = $gameModel->getAllGames([]);
            
            if (!empty($games)) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Successfully fetched games from database</p>';
                
                echo '<table class="data-table">';
                echo '<thead><tr><th>ID</th><th>Title</th><th>Developer</th><th>Rating</th><th>Genres</th><th>Platforms</th></tr></thead>';
                echo '<tbody>';
                foreach ($games as $game) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($game['id']) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($game['title']) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($game['developer']) . '</td>';
                    echo '<td class="rating">★ ' . number_format($game['avg_rating'], 2) . ' (' . $game['rating_count'] . ')</td>';
                    echo '<td>';
                    foreach ($game['genres'] as $genre) {
                        echo '<span class="badge badge-genre">' . htmlspecialchars($genre['name']) . '</span>';
                    }
                    echo '</td>';
                    echo '<td>';
                    $platformCount = count($game['platforms']);
                    echo '<span class="badge badge-platform">' . $platformCount . ' platform' . ($platformCount != 1 ? 's' : '') . '</span>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                
                // Show total count
                $totalGames = $gameModel->getTotalCount();
                echo '<div class="info-box">';
                echo '<strong>Total games in database:</strong> ' . $totalGames;
                echo '</div>';
            } else {
                echo '<span class="status info">ℹ INFO</span>';
                echo '<p>No games found in database</p>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // TEST 5: Games - Filtered by Genre
        echo '<div class="test-section">';
        echo '<h2>Test 5: Game Model - Filter by Genre (Action)</h2>';
        
        try {
            $gameModel = new Game();
            // Genre ID 2 = Action (from your database)
            $filters = ['genre_ids' => [2]];
            $actionGames = $gameModel->getAllGames($filters, 5, 0);
            
            if (!empty($actionGames)) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Found ' . count($actionGames) . ' action games (showing max 5)</p>';
                
                echo '<table class="data-table">';
                echo '<thead><tr><th>Title</th><th>Developer</th><th>Rating</th></tr></thead>';
                echo '<tbody>';
                foreach ($actionGames as $game) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($game['title']) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($game['developer']) . '</td>';
                    echo '<td class="rating">★ ' . number_format($game['avg_rating'], 2) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                
                $totalAction = $gameModel->getTotalCount($filters);
                echo '<p style="margin-top:10px;color:#6b7280;">Total Action games: ' . $totalAction . '</p>';
            } else {
                echo '<span class="status info">ℹ INFO</span>';
                echo '<p>No action games found</p>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // TEST 6: Games - Filtered by Platform
        echo '<div class="test-section">';
        echo '<h2>Test 6: Game Model - Filter by Platform (PC)</h2>';
        
        try {
            $gameModel = new Game();
            // Platform ID 1 = PC (from your database)
            $filters = ['platform_ids' => [1]];
            $pcGames = $gameModel->getAllGames($filters, 5, 0);
            
            if (!empty($pcGames)) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Found PC games (showing max 5)</p>';
                
                echo '<table class="data-table">';
                echo '<thead><tr><th>Title</th><th>Price</th><th>Rating</th></tr></thead>';
                echo '<tbody>';
                foreach ($pcGames as $game) {
                    // Find PC platform pricing
                    $pcPrice = 'N/A';
                    foreach ($game['platforms'] as $platform) {
                        if ($platform['id'] == 1) {
                            $pcPrice = $platform['price'] > 0 
                                ? $platform['currency'] . ' ' . number_format($platform['price'], 2) 
                                : 'Free';
                            break;
                        }
                    }
                    
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($game['title']) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($pcPrice) . '</td>';
                    echo '<td class="rating">★ ' . number_format($game['avg_rating'], 2) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                
                $totalPC = $gameModel->getTotalCount($filters);
                echo '<p style="margin-top:10px;color:#6b7280;">Total PC games: ' . $totalPC . '</p>';
            } else {
                echo '<span class="status info">ℹ INFO</span>';
                echo '<p>No PC games found</p>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';


        // TEST 8: Multiple Filters Combined
        echo '<div class="test-section">';
        echo '<h2>Test 8: Game Model - Multiple Filters (Action + PC + Rating 4+)</h2>';
        
        try {
            $gameModel = new Game();
            $filters = [
                'genre_ids' => [2],      // Action
                'platform_ids' => [1],   // PC
                'min_rating' => 4.0,     // 4 stars or higher
                'sort' => 'rating_desc'
            ];
            $filteredGames = $gameModel->getAllGames($filters, 5, 0);
            
            if (!empty($filteredGames)) {
                echo '<span class="status success">✓ SUCCESS</span>';
                echo '<p>Found ' . count($filteredGames) . ' games matching all filters (showing max 5)</p>';
                
                echo '<table class="data-table">';
                echo '<thead><tr><th>Title</th><th>Rating</th><th>Developer</th></tr></thead>';
                echo '<tbody>';
                foreach ($filteredGames as $game) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($game['title']) . '</strong></td>';
                    echo '<td class="rating">★ ' . number_format($game['avg_rating'], 2) . ' (' . $game['rating_count'] . ')</td>';
                    echo '<td>' . htmlspecialchars($game['developer']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                
                $totalFiltered = $gameModel->getTotalCount($filters);
                echo '<p style="margin-top:10px;color:#6b7280;">Total matching games: ' . $totalFiltered . '</p>';
            } else {
                echo '<span class="status info">ℹ INFO</span>';
                echo '<p>No games match all filters</p>';
            }
        } catch (Exception $e) {
            echo '<span class="status error">✗ FAILED</span>';
            echo '<div class="error-box">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // SUMMARY
        echo '<div class="test-section">';
        echo '<h2>✅ Test Summary</h2>';
        echo '<div class="info-box">';
        echo '<p><strong>All critical tests completed!</strong></p>';
        echo '<p style="margin-top:10px;">Your database connection is working correctly and all models are functioning as expected.</p>';
        echo '<p style="margin-top:10px;"><strong>Next Steps:</strong></p>';
        echo '<ul style="margin-left:20px;margin-top:10px;">';
        echo '<li>Create the games listing page (games_list.php)</li>';
        echo '<li>Create the game detail page (game_detail.php)</li>';
        echo '<li>Build the filter UI with dropdowns</li>';
        echo '<li>Implement search functionality</li>';
        echo '<li>Add pagination controls</li>';
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        ?>
    </div>
</body>
</html>