<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';
require_once 'config.php';
// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\BoardGame;

// Create app from App class
$app = new App();
$boardgame = new BoardGame();
$items = $boardgame->get();

// Checking if the user is logged in
$isauthenticated = false;
if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
    $isauthenticated = true;
    
    // Get user's favorites if logged in
    try {
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Get user ID
        $user_stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
        $user_stmt->execute([$_SESSION['email']]);
        $user_id = $user_stmt->fetchColumn();

        if ($user_id) {
            // Get user's favorites
            $favorite_stmt = $db->prepare("
                SELECT boardgame_id 
                FROM Favourite 
                WHERE user_id = ?
            ");
            $favorite_stmt->execute([$user_id]);
            $favorite_ids = $favorite_stmt->fetchAll(PDO::FETCH_COLUMN);

            // Add is_favorited flag to each game
            foreach ($items as &$item) {
                $item['is_favorited'] = in_array($item['id'], $favorite_ids);
            }
        }
    } catch (Exception $e) {
        error_log("Error getting favorites: " . $e->getMessage());
    }
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('games.twig');

// Render the output
echo $template->render([
    'items' => $items,
    'loggedin' => $isauthenticated,
    'current_page' => 'games'
]); 