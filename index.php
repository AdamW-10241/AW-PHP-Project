<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'session_helper.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

// Get database connection
$conn = getDBConnection();

// Get featured game
$featuredGameQuery = "
    SELECT 
        BoardGame.id,
        BoardGame.title as name,
        BoardGame.tagline,
        BoardGame.description,
        BoardGame.player_range,
        BoardGame.age_range,
        BoardGame.playtime_range,
        BoardGame.image as image_url,
        BoardGame.tags,
        (SELECT GROUP_CONCAT(DISTINCT Publisher.name SEPARATOR ', ') 
        FROM BoardGame_Publisher 
        INNER JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.publisher_id
        WHERE BoardGame_Publisher.boardgame_id = BoardGame.id) AS publishers,
        (SELECT GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name) SEPARATOR ', ') 
        FROM BoardGame_Designer 
        INNER JOIN Designer ON BoardGame_Designer.designer_id = Designer.designer_id
        WHERE BoardGame_Designer.boardgame_id = BoardGame.id) AS designers,
        (SELECT GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name) SEPARATOR ', ') 
        FROM BoardGame_Artist 
        INNER JOIN Artist ON BoardGame_Artist.artist_id = Artist.artist_id
        WHERE BoardGame_Artist.boardgame_id = BoardGame.id) AS artists
    FROM BoardGame
    WHERE BoardGame.visible = 1
    ORDER BY BoardGame.id ASC
    LIMIT 1";
$featuredGame = $conn->query($featuredGameQuery)->fetch_assoc();

// Get popular games
$popularGamesQuery = "
    SELECT 
        BoardGame.id,
        BoardGame.title as name,
        BoardGame.tagline,
        BoardGame.description,
        BoardGame.player_range,
        BoardGame.age_range,
        BoardGame.playtime_range,
        BoardGame.image as image_url,
        BoardGame.tags,
        (SELECT GROUP_CONCAT(DISTINCT Publisher.name SEPARATOR ', ') 
        FROM BoardGame_Publisher 
        INNER JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.publisher_id
        WHERE BoardGame_Publisher.boardgame_id = BoardGame.id) AS publishers,
        (SELECT GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name) SEPARATOR ', ') 
        FROM BoardGame_Designer 
        INNER JOIN Designer ON BoardGame_Designer.designer_id = Designer.designer_id
        WHERE BoardGame_Designer.boardgame_id = BoardGame.id) AS designers,
        (SELECT GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name) SEPARATOR ', ') 
        FROM BoardGame_Artist 
        INNER JOIN Artist ON BoardGame_Artist.artist_id = Artist.artist_id
        WHERE BoardGame_Artist.boardgame_id = BoardGame.id) AS artists
    FROM BoardGame
    WHERE BoardGame.visible = 1
    ORDER BY BoardGame.id ASC
    LIMIT 3";
$popularGames = $conn->query($popularGamesQuery)->fetch_all(MYSQLI_ASSOC);

// Update image paths
if ($featuredGame) {
    $featuredGame['image_url'] = '/assets/cover_images/' . basename($featuredGame['image_url']);
}

foreach ($popularGames as &$game) {
    $game['image_url'] = '/assets/cover_images/' . basename($game['image_url']);
}

// Get latest news
$latestNews = [];
try {
    $newsQuery = "SELECT * FROM news ORDER BY created_at DESC LIMIT 3";
    $newsResult = $conn->query($newsQuery);
    if ($newsResult) {
        while ($row = $newsResult->fetch_assoc()) {
            $latestNews[] = $row;
        }
    }
} catch (Exception $e) {
    // Table doesn't exist or other error, continue with empty news array
}

// Render the template with data
echo $twig->render('index.twig', [
    'loggedin' => isLoggedIn(),
    'featuredGame' => $featuredGame,
    'popularGames' => $popularGames,
    'latestNews' => $latestNews
]);
?>

