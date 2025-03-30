<?php
// Start session
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

// Connect to database
$db = getDBConnection();

// Get featured game
$stmt = $db->query("
    SELECT 
        BoardGame.*,
        GROUP_CONCAT(DISTINCT Publisher.name) as publishers,
        GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name)) as designers,
        GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name)) as artists
    FROM BoardGame
    LEFT JOIN BoardGame_Publisher ON BoardGame.id = BoardGame_Publisher.boardgame_id
    LEFT JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.publisher_id
    LEFT JOIN BoardGame_Designer ON BoardGame.id = BoardGame_Designer.boardgame_id
    LEFT JOIN Designer ON BoardGame_Designer.designer_id = Designer.designer_id
    LEFT JOIN BoardGame_Artist ON BoardGame.id = BoardGame_Artist.boardgame_id
    LEFT JOIN Artist ON BoardGame_Artist.artist_id = Artist.artist_id
    WHERE BoardGame.visible = 1
    GROUP BY BoardGame.id
    ORDER BY BoardGame.year DESC
    LIMIT 1
");
$featured_game = $stmt->fetch_assoc();

// Get popular games
$stmt = $db->query("
    SELECT * FROM BoardGame 
    WHERE visible = 1 
    ORDER BY RAND() 
    LIMIT 4
");

// Debug: Check for query errors
if ($db->error) {
    error_log("Database error: " . $db->error);
}

$popular_games = [];
while ($row = $stmt->fetch_assoc()) {
    $popular_games[] = $row;
}

// Debug: Print detailed information
error_log("Number of popular games: " . count($popular_games));
error_log("Database error (if any): " . $db->error);
error_log("Database errno (if any): " . $db->errno);

if (count($popular_games) > 0) {
    error_log("First popular game: " . print_r($popular_games[0], true));
} else {
    // Check if there are any games at all
    $check_stmt = $db->query("SELECT COUNT(*) as count FROM BoardGame WHERE visible = 1");
    $count = $check_stmt->fetch_assoc()['count'];
    error_log("Total number of visible games in database: " . $count);
}

// Update image paths
foreach ($popular_games as &$game) {
    $game['image'] = '/assets/cover_images/' . $game['image'];
}
if ($featured_game) {
    $featured_game['image'] = '/assets/cover_images/' . $featured_game['image'];
}

// Get latest news
try {
    $stmt = $db->query("
        SELECT * FROM news 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $news = [];
    while ($row = $stmt->fetch_assoc()) {
        $news[] = $row;
    }
} catch (Exception $e) {
    // Table doesn't exist or other error, continue with empty news array
    $news = [];
}

// Render template
echo $twig->render('index.twig', [
    'loggedin' => isLoggedIn(),
    'featured_game' => $featured_game,
    'popular_games' => $popular_games,
    'news' => $news,
    'current_page' => 'home'
]);
?>

