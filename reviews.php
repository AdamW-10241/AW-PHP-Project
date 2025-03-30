<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Adam\AwPhpProject\BoardGame;
use Adam\AwPhpProject\Account;

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
                    $account = new Account();
                    $user = $account->getUserByEmail($_SESSION['email']);
                    
                    if (!$user) {
                        throw new Exception("User not found");
                    }

                    $boardGame = new BoardGame();
                    $boardGame->deleteReview($review_id, $user['id']);

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

                    $account = new Account();
                    $user = $account->getUserByEmail($_SESSION['email']);
                    
                    if (!$user) {
                        throw new Exception("User not found");
                    }

                    $boardGame = new BoardGame();
                    $boardGame->updateReview($review_id, $user['id'], $rating, $review_text);

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

                $account = new Account();
                $user = $account->getUserByEmail($_SESSION['email']);
                
                if (!$user) {
                    throw new Exception("User not found");
                }

                $boardGame = new BoardGame();
                $boardGame->addReview($user['id'], $game_id, $rating, $review_text);

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
    $boardGame = new BoardGame();
    $reviews = $boardGame->getReviews();
    $games = $boardGame->getGamesForReview();

    // Render template
    echo $twig->render('reviews.twig', [
        'loggedin' => isLoggedIn(),
        'reviews' => $reviews,
        'games' => $games,
        'error' => $error,
        'redirect' => $redirect,
        'session_email' => $_SESSION['email'] ?? null,
        'current_page' => 'reviews'
    ]);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

