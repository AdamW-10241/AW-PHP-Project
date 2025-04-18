<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Adam\AwPhpProject\BoardGame;
use Adam\AwPhpProject\Account;

// Initialize BoardGame instance
$boardGame = new BoardGame();

$redirect = false;
$error = null;
$success = null;

// Get user ID if logged in
$user_id = null;
if (isLoggedIn()) {
    $account = new Account();
    if ($account->getUserByEmail($_SESSION['email'])) {
        $user_id = $account->getId();
    }
}

// Handle review submission, editing, and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['email'])) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete') {
            // Handle delete review
            $review_id = $_POST['review_id'] ?? null;

            if ($review_id) {
                try {
                    $account = new Account();
                    if (!$account->getUserByEmail($_SESSION['email'])) {
                        throw new Exception("User not found");
                    }

                    $boardGame->deleteReview($review_id, $account->getId());

                    // Redirect to the same page to show the updated reviews
                    header("Location: reviews.php");
                    exit;
                } catch (Exception $e) {
                    $error = "Error deleting review: " . $e->getMessage();
                }
            }
        } else if ($_POST['action'] === 'edit') {
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
                    if (!$account->getUserByEmail($_SESSION['email'])) {
                        throw new Exception("User not found");
                    }

                    $boardGame->updateReview($review_id, $account->getId(), $rating, $review_text);

                    // Redirect to the same page to show the updated review
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
                if (!$account->getUserByEmail($_SESSION['email'])) {
                    throw new Exception("User not found");
                }

                $boardGame->addReview($game_id, $account->getId(), $rating, $review_text);

                // Redirect to the same page to show the new review
                header("Location: reviews.php");
                exit;
            } catch (Exception $e) {
                $error = "Error adding review: " . $e->getMessage();
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
    $reviews = $boardGame->getReviews();
    $games = $boardGame->getGamesForReview();

    // Render template
    echo $twig->render('reviews.twig', [
        'loggedin' => isLoggedIn(),
        'is_admin' => isAdmin(),
        'reviews' => $reviews,
        'games' => $games,
        'error' => $error,
        'success' => $success,
        'redirect' => $redirect,
        'session_email' => $_SESSION['email'] ?? null,
        'session_username' => $_SESSION['username'] ?? null,
        'current_page' => 'reviews',
        'user_id' => $user_id
    ]);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

