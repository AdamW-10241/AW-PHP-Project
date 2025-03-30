<?php
session_start();
require_once 'vendor/autoload.php';
// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\BoardGame;

// Create app from App class first to load environment variables
$app = new App();
$boardgame = new BoardGame();
$detail = array();
$reviews = array();
$error = null;

// Remove the incorrect request method check that was causing the redirect
if (!isset($_GET["id"])) {
    // Redirect to home page only if no ID is provided
    header("location: index.php");
    exit;
}

if ( $_GET['id'] ) {
    $detail = $boardgame -> getDetail( $_GET['id'] );
    
    // Get reviews for this game
    try {
        $db = new PDO(
            "mysql:host=" . $_ENV['DBHOST'] . ";dbname=" . $_ENV['DBNAME'] . ";charset=utf8mb4",
            $_ENV['DBUSER'],
            $_ENV['DBPASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $db->prepare("
            SELECT 
                r.*,
                u.email as user_name
            FROM reviews r
            JOIN Account u ON r.user_id = u.id
            WHERE r.game_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$_GET['id']]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error but don't show it to users
        error_log("Error fetching reviews: " . $e->getMessage());
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['email'])) {
    $game_id = $_POST['game_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review_text = $_POST['review_text'] ?? null;

    if ($game_id && $rating && $review_text) {
        try {
            // Validate rating
            $rating = (int)$rating;
            if ($rating < 1 || $rating > 5) {
                throw new Exception("Rating must be between 1 and 5");
            }

            $db = new PDO(
                "mysql:host=" . $_ENV['DBHOST'] . ";dbname=" . $_ENV['DBNAME'] . ";charset=utf8mb4",
                $_ENV['DBUSER'],
                $_ENV['DBPASSWORD'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Get user ID
            $stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
            $stmt->execute([$_SESSION['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception("User not found");
            }

            // Check if user already reviewed this game
            $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND game_id = ?");
            $stmt->execute([$user['id'], $game_id]);
            if ($stmt->fetch()) {
                throw new Exception("You have already reviewed this game");
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

            // Redirect to the same page to show the new review
            header("Location: detail.php?id=" . $game_id);
            exit;
        } catch (Exception $e) {
            $error = "Error submitting review: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields";
    }
}

// Checking if the user is logged in
$isauthenticated = false;
if ( isset( $_SESSION['email'] ) ) {
    $isauthenticated = true;
}

$page_title = "Detail for " . $detail['title'];

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'detail.twig' );

// Render the output
echo $template -> render( [
    'detail' => $detail,
    'loggedin' => $isauthenticated,
    'reviews' => $reviews,
    'error' => $error
] );
?>