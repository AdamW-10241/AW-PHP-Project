<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$redirect = false;
$error = null;

// Handle review submission, editing, and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete') {
            // Handle delete review
            $review_id = $_POST['review_id'] ?? null;

            if ($review_id) {
                try {
                    $db = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                        DB_USER,
                        DB_PASS,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                        ]
                    );

                    // Get user ID
                    $stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
                    $stmt->execute([$_SESSION['email']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user) {
                        throw new Exception("User not found");
                    }

                    // Verify the review belongs to the user
                    $stmt = $db->prepare("SELECT id FROM reviews WHERE id = ? AND user_id = ?");
                    $stmt->execute([$review_id, $user['id']]);
                    if (!$stmt->fetch()) {
                        throw new Exception("You can only delete your own reviews");
                    }

                    // Delete review
                    $stmt = $db->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
                    $stmt->execute([$review_id, $user['id']]);

                    // Redirect after successful deletion
                    header("Location: reviews.php");
                    exit;
                } catch (Exception $e) {
                    $error = "Error deleting review: " . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'edit') {
            // Handle edit review
            $review_id = $_POST['review_id'] ?? null;
            $rating = $_POST['rating'] ?? null;
            $review_text = $_POST['review_text'] ?? null;

            if ($review_id && $rating && $review_text) {
                try {
                    // Validate rating
                    $rating = (int)$rating;
                    if ($rating < 1 || $rating > 5) {
                        throw new Exception("Rating must be between 1 and 5");
                    }

                    $db = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                        DB_USER,
                        DB_PASS,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                        ]
                    );

                    // Get user ID
                    $stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
                    $stmt->execute([$_SESSION['email']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user) {
                        throw new Exception("User not found");
                    }

                    // Verify the review belongs to the user
                    $stmt = $db->prepare("SELECT id FROM reviews WHERE id = ? AND user_id = ?");
                    $stmt->execute([$review_id, $user['id']]);
                    if (!$stmt->fetch()) {
                        throw new Exception("You can only edit your own reviews");
                    }

                    // Update review
                    $stmt = $db->prepare("
                        UPDATE reviews 
                        SET rating = ?, comment = ?
                        WHERE id = ? AND user_id = ?
                    ");

                    $stmt->execute([
                        $rating,
                        $review_text,
                        $review_id,
                        $user['id']
                    ]);

                    // Redirect after successful update
                    header("Location: reviews.php");
                    exit;
                } catch (Exception $e) {
                    $error = "Error updating review: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all fields";
            }
        }
    } else {
        // Handle new review submission
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
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ]
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

                // Redirect after successful submission
                header("Location: reviews.php");
                exit;
            } catch (Exception $e) {
                $error = "Error submitting review: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all fields";
        }
    }
}

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
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
        'error' => $error,
        'redirect' => $redirect,
        'session_email' => $_SESSION['email'] ?? null
    ]);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

