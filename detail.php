<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'session_helper.php';
require_once 'src/BoardGame.php';
require_once 'src/Account.php';
require_once 'src/Security.php';

// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\BoardGame;
use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\Security;

// Create app from App class first to load environment variables
$app = new App();
$boardgame = new BoardGame();
$account = new Account();
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
        $reviews = $boardgame->getReviewsForGame($_GET['id']);
    } catch (Exception $e) {
        // Log error but don't show it to users
        error_log("Error fetching reviews: " . $e->getMessage());
    }

    // Get similar games
    try {
        $similarGames = $boardgame->getSimilarGames($_GET['id']);
    } catch (Exception $e) {
        error_log("Error fetching similar games: " . $e->getMessage());
        $similarGames = [];
    }

    // Check if user has already reviewed this game
    $has_reviewed = false;
    if (isset($_SESSION['email'])) {
        try {
            $user = $account->getUserByEmail($_SESSION['email']);
            if ($user) {
                $has_reviewed = $boardgame->hasUserReviewed($user['id'], $_GET['id']);
            }
        } catch (Exception $e) {
            error_log("Error checking user review: " . $e->getMessage());
        }
    }

    // Check if the game is favorited by the current user
    $is_favorited = false;
    if (isLoggedIn() && isset($_SESSION['email'])) {
        try {
            $user = $account->getUserByEmail($_SESSION['email']);
            if ($user) {
                $is_favorited = $boardgame->isFavorited($user['id'], $_GET['id']);
            }
        } catch (Exception $e) {
            error_log("Error checking favorite status: " . $e->getMessage());
        }
    }
}

// Handle review submission, editing, and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['email'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !Security::validateToken($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'delete') {
                // Handle delete review
                $review_id = $_POST['review_id'] ?? null;

                if ($review_id) {
                    try {
                        $user = $account->getUserByEmail($_SESSION['email']);
                        
                        if (!$user) {
                            throw new Exception("User not found");
                        }

                        $boardgame->deleteReview($review_id, $user['id']);

                        // Redirect to the same page to show the updated reviews
                        header("Location: detail.php?id=" . $_GET['id']);
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

                        $user = $account->getUserByEmail($_SESSION['email']);
                        
                        if (!$user) {
                            throw new Exception("User not found");
                        }

                        $boardgame->updateReview($review_id, $user['id'], $rating, $review_text);

                        // Redirect to the same page to show the updated review
                        header("Location: detail.php?id=" . $_GET['id']);
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

                    $user = $account->getUserByEmail($_SESSION['email']);
                    
                    if (!$user) {
                        throw new Exception("User not found");
                    }

                    $boardgame->addReview($user['id'], $game_id, $rating, $review_text);

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

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Render the output
echo $twig->render('detail.twig', [
    'detail' => $detail,
    'loggedin' => $isauthenticated,
    'reviews' => $reviews,
    'error' => $error,
    'session_email' => $_SESSION['email'] ?? null,
    'session_username' => $_SESSION['username'] ?? null,
    'has_reviewed' => $has_reviewed,
    'is_favorited' => $is_favorited,
    'similar_games' => $similarGames
]);
?>