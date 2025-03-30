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
$db = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

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
$featured_game = $stmt->fetch(PDO::FETCH_ASSOC);

// Get popular games
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
    LIMIT 4
");
$popular_games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update image paths
foreach ($popular_games as &$game) {
    $game['image'] = '/images/games/' . $game['image'];
}
if ($featured_game) {
    $featured_game['image'] = '/images/games/' . $featured_game['image'];
}

// Get latest news
try {
    $stmt = $db->query("
        SELECT * FROM news 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist or other error, continue with empty news array
    $news = [];
}

// Render template
echo $twig->render('index.twig', [
    'loggedin' => isLoggedIn(),
    'featured_game' => $featured_game,
    'popular_games' => $popular_games,
    'news' => $news
]);
?>

