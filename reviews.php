<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$redirect = false;

// Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $game_id = $_POST['game_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review_text = $_POST['review_text'] ?? null;

    if ($game_id && $rating && $review_text) {
        try {
            $db = new PDO(
                "mysql:host=db;dbname=mariadb;charset=utf8mb4",
                "mariadb",
                "mariadb",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Get user ID
            $stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
            $stmt->execute([$_SESSION['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception("User not found");
            }

            // Save review
            $stmt = $db->prepare("
                INSERT INTO reviews (user_id, game_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $user['id'],
                $game_id,
                $rating,
                $review_text
            ]);

            $redirect = true;
        } catch (Exception $e) {
            $error = "Error submitting review: " . $e->getMessage();
        }
    }
}

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

try {
    $db = new PDO(
        "mysql:host=db;dbname=mariadb;charset=utf8mb4",
        "mariadb",
        "mariadb",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get reviews with user and game details
    $stmt = $db->query("
        SELECT 
            r.*,
            u.email as user_name,
            g.title as game_title
        FROM reviews r
        JOIN Account u ON r.user_id = u.id
        JOIN BoardGame g ON r.game_id = g.id
        ORDER BY r.created_at DESC
    ");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get games for the review form
    $stmt = $db->query("
        SELECT id, title 
        FROM BoardGame 
        WHERE visible = 1 
        ORDER BY title ASC
    ");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Render template
    echo $twig->render('reviews.twig', [
        'loggedin' => isLoggedIn(),
        'reviews' => $reviews,
        'games' => $games,
        'error' => $error ?? null,
        'redirect' => $redirect
    ]);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

