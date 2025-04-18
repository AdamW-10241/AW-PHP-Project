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

// Get search query and filters
$query = $_GET['q'] ?? '';
$franchise = $_GET['franchise'] ?? '';
$brand = $_GET['brand'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 100;
$genre = $_GET['genre'] ?? '';
$player_range = $_GET['player_range'] ?? '';
$age_range = $_GET['age_range'] ?? '';
$min_playtime = $_GET['min_playtime'] ?? 0;
$max_playtime = $_GET['max_playtime'] ?? 240;

// Initialize variables
$data = [
    'loggedin' => isLoggedIn(),
    'is_admin' => isAdmin(),
    'query' => $query,
    'franchise' => $franchise,
    'brand' => $brand,
    'min_price' => $min_price,
    'max_price' => $max_price,
    'genre' => $genre,
    'player_range' => $player_range,
    'age_range' => $age_range,
    'min_playtime' => $min_playtime,
    'max_playtime' => $max_playtime,
    'results' => []
];

// Get unique values for filters
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get unique values for each filter
    $data['franchises'] = $db->query("SELECT DISTINCT franchise FROM BoardGame WHERE franchise IS NOT NULL ORDER BY franchise")->fetchAll(PDO::FETCH_COLUMN);
    $data['brands'] = $db->query("SELECT DISTINCT brand FROM BoardGame WHERE brand IS NOT NULL ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
    $data['genres'] = $db->query("SELECT DISTINCT genre FROM BoardGame WHERE genre IS NOT NULL ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

    // Sort player ranges naturally (e.g., "1-2", "2-4", "4-6", etc.)
    $data['player_ranges'] = $db->query("
        SELECT DISTINCT player_range 
        FROM BoardGame 
        WHERE player_range IS NOT NULL 
        ORDER BY 
            CAST(SUBSTRING_INDEX(player_range, '-', 1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(player_range, '-', -1) AS UNSIGNED)
    ")->fetchAll(PDO::FETCH_COLUMN);

    // Sort age ranges naturally (e.g., "8+", "10+", "12+", etc.)
    $data['age_ranges'] = $db->query("
        SELECT DISTINCT age_range 
        FROM BoardGame 
        WHERE age_range IS NOT NULL 
        ORDER BY CAST(REPLACE(age_range, '+', '') AS UNSIGNED)
    ")->fetchAll(PDO::FETCH_COLUMN);

    // Build the SQL query
    $sql = "SELECT * FROM BoardGame WHERE 1=1";
    $params = [];

    if ($query) {
        $sql .= " AND (title LIKE ? OR description LIKE ? OR publisher LIKE ?)";
        $searchTerm = "%$query%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($franchise) {
        $sql .= " AND franchise = ?";
        $params[] = $franchise;
    }

    if ($brand) {
        $sql .= " AND brand = ?";
        $params[] = $brand;
    }

    if ($min_price || $max_price) {
        $sql .= " AND (
            (min_price <= ? AND max_price >= ?) OR  -- Game range contains selected range
            (min_price >= ? AND min_price <= ?) OR  -- Game starts within selected range
            (max_price >= ? AND max_price <= ?)     -- Game ends within selected range
        )";
        $params[] = $max_price;  // For first condition
        $params[] = $min_price;  // For first condition
        $params[] = $min_price;  // For second condition
        $params[] = $max_price;  // For second condition
        $params[] = $min_price;  // For third condition
        $params[] = $max_price;  // For third condition
    }

    if ($genre) {
        $sql .= " AND genre = ?";
        $params[] = $genre;
    }

    if ($player_range) {
        $sql .= " AND player_range = ?";
        $params[] = $player_range;
    }

    if ($age_range) {
        $sql .= " AND age_range = ?";
        $params[] = $age_range;
    }

    if ($min_playtime || $max_playtime) {
        $sql .= " AND (
            (min_playtime <= ? AND max_playtime >= ?) OR  -- Game range contains selected range
            (min_playtime >= ? AND min_playtime <= ?) OR  -- Game starts within selected range
            (max_playtime >= ? AND max_playtime <= ?)     -- Game ends within selected range
        )";
        $params[] = $max_playtime;  // For first condition
        $params[] = $min_playtime;  // For first condition
        $params[] = $min_playtime;  // For second condition
        $params[] = $max_playtime;  // For second condition
        $params[] = $min_playtime;  // For third condition
        $params[] = $max_playtime;  // For third condition
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update image paths to include the correct directory
    foreach ($results as &$game) {
        $game['image'] = 'assets/cover_images/' . $game['image'];
    }

    $data['results'] = $results;
} catch (PDOException $e) {
    $data['error'] = "Error searching games: " . $e->getMessage();
}

// Render the template
echo $twig->render('search.twig', [
    'loggedin' => isLoggedIn(),
    'is_admin' => isAdmin(),
    'query' => $query,
    'results' => $results,
    'franchise' => $franchise,
    'brand' => $brand,
    'min_price' => $min_price,
    'max_price' => $max_price,
    'genre' => $genre,
    'player_range' => $player_range,
    'age_range' => $age_range,
    'min_playtime' => $min_playtime,
    'max_playtime' => $max_playtime,
    'franchises' => $data['franchises'],
    'brands' => $data['brands'],
    'genres' => $data['genres'],
    'player_ranges' => $data['player_ranges'],
    'age_ranges' => $data['age_ranges']
]); 