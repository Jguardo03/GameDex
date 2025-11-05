<?php
// Reusable filter component
// Usage (in any page):
// include __DIR__ . '/components/filterbox.php';
?>

<div class="filter-box">
    <div class="icon">
        <i class="fa-solid fa-filter"></i>
        <p>Filters</p>
    </div>
    <div class="filter-box-elements">
        <div class="element-dropdown">
        <p class="label">Genres</p>
        <?php
            require_once __DIR__ . '/../models/genre.php';
            $genreModel = new Genre();
            $genres = $genreModel->getAllWithCount();
            echo '<select name="genre" id="genre-dropdown">';
            echo '<option value="">All</option>';
            foreach ($genres as $genre) {
                echo '<option value="' . htmlspecialchars($genre['id']) . '">' . htmlspecialchars($genre['name']) . ' (' . htmlspecialchars($genre['game_count']) . ')</option>';
            }
            echo '</select>';
        ?>
        </div>
        <div class="element-dropdown">
        <p class="label">Platforms</p>
        <?php
            require_once __DIR__ . '/../models/platform.php';
            $platformModel = new Platform();
            $platforms = $platformModel->getAllWithCount();
            echo '<select name="platform" id="platform-dropdown">';
            echo '<option value="">All</option>';
            foreach ($platforms as $platform) {
                echo '<option value="' . htmlspecialchars($platform['id']) . '">' . htmlspecialchars($platform['name']) . ' (' . htmlspecialchars($platform['game_count']) . ')</option>';
            }
            echo '</select>';
        ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#genre-dropdown, #platform-dropdown').on('change', function() {
            var selectedGenre = $('#genre-dropdown').val();
            var selectedPlatform = $('#platform-dropdown').val();
            // Implement filtering logic here, e.g., make an AJAX call to fetch filtered games
            console.log('Selected Genre: ' + selectedGenre);
            console.log('Selected Platform: ' + selectedPlatform);
            // AJAX call to refresh the game list
            $.ajax({
                url: 'index.php', // Corrected URL
                type: 'GET', // Changed to GET for simplicity and bookmarking
                data:{
                    genre: selectedGenre,
                    platform: selectedPlatform,
                    ajax: 1 // Flag to identify AJAX request
                },
                success:function(response){
                    // Replace the content of the game-catalog-container with the response
                    $('#game-catalog-container').html(response);
                },
                error:function(xhr,status,error){
                    console.error("AJAX Error: " + status + " - " + error);
                }
            });
        });
    });
</script>
