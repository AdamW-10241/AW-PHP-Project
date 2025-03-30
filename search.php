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
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Debug: Print the search query
        echo "<!-- Debug: Searching for: " . htmlspecialchars($query) . " -->\n";

        $stmt = $db->prepare("
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
            AND (
                LOWER(BoardGame.title) LIKE LOWER(?) 
                OR LOWER(BoardGame.description) LIKE LOWER(?)
                OR LOWER(Publisher.name) LIKE LOWER(?)
                OR LOWER(CONCAT(Designer.first_name, ' ', Designer.last_name)) LIKE LOWER(?)
                OR LOWER(CONCAT(Artist.first_name, ' ', Artist.last_name)) LIKE LOWER(?)
            )
            GROUP BY 
                BoardGame.id,
                BoardGame.title,
                BoardGame.tagline,
                BoardGame.year,
                BoardGame.description,
                BoardGame.player_range,
                BoardGame.age_range,
                BoardGame.playtime_range,
                BoardGame.image,
                BoardGame.tags,
                BoardGame.visible,
                BoardGame.created_at
            ORDER BY BoardGame.title ASC
        ");

        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Print the SQL query and results count
        echo "<!-- Debug: Found " . count($results) . " results -->\n";
        echo "<!-- Debug: SQL Query: " . htmlspecialchars($stmt->queryString) . " -->\n";
        echo "<!-- Debug: Search term: " . htmlspecialchars($searchTerm) . " -->\n";

        // Debug: Print first result if any
        if (count($results) > 0) {
            echo "<!-- Debug: First result: " . htmlspecialchars(print_r($results[0], true)) . " -->\n";
        }

        // Update image paths to include the correct directory
        foreach ($results as &$game) {
            $game['image'] = 'assets/cover_images/' . $game['image'];
        }

        $data['results'] = $results;
    } catch (PDOException $e) {
        $data['error'] = "Error searching games: " . $e->getMessage();
        // Debug: Print any errors
        echo "<!-- Debug: Error: " . htmlspecialchars($e->getMessage()) . " -->\n";
    }
}

// Render template
echo $twig->render('search.twig', $data); 