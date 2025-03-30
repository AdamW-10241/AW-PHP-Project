<?php
// Start session
session_start();
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

// Get search query
$query = $_GET['q'] ?? '';

// Initialize variables
$data = [
    'loggedin' => isLoggedIn(),
    'query' => $query,
    'results' => []
];

// Search if query exists
if ($query) {
    try {
        $db = new PDO(
            "mysql:host=db;dbname=mariadb;charset=utf8mb4",
            "mariadb",
            "mariadb",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $db->prepare("
            SELECT 
                BoardGame.*,
                GROUP_CONCAT(DISTINCT Publisher.name) as publishers,
                GROUP_CONCAT(DISTINCT Designer.name) as designers,
                GROUP_CONCAT(DISTINCT Artist.name) as artists
            FROM BoardGame
            LEFT JOIN BoardGame_Publisher ON BoardGame.id = BoardGame_Publisher.game_id
            LEFT JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.id
            LEFT JOIN BoardGame_Designer ON BoardGame.id = BoardGame_Designer.game_id
            LEFT JOIN Designer ON BoardGame_Designer.designer_id = Designer.id
            LEFT JOIN BoardGame_Artist ON BoardGame.id = BoardGame_Artist.game_id
            LEFT JOIN Artist ON BoardGame_Artist.artist_id = Artist.id
            WHERE BoardGame.visible = 1
            AND (BoardGame.title LIKE ? OR BoardGame.description LIKE ?)
            GROUP BY BoardGame.id
            ORDER BY BoardGame.rating DESC
        ");

        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update image paths
        foreach ($results as &$game) {
            $game['image'] = '/images/games/' . $game['image'];
        }

        $data['results'] = $results;
    } catch (PDOException $e) {
        $data['error'] = "Error searching games: " . $e->getMessage();
    }
}

// Render template
echo $twig->render('search.twig', $data); 