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
            if ($account->getUserByEmail($_SESSION['email'])) {
                $has_reviewed = $boardgame->hasUserReviewed($account->getId(), $_GET['id']);
            }
        } catch (Exception $e) {
            error_log("Error checking user review: " . $e->getMessage());
        }
    }

    // Check if the game is favorited by the current user
    $is_favorited = false;
    if (isLoggedIn() && isset($_SESSION['email'])) {
        try {
            if ($account->getUserByEmail($_SESSION['email'])) {
                $is_favorited = $boardgame->isFavorited($account->getId(), $_GET['id']);
            }
        } catch (Exception $e) {
            error_log("Error checking favorite status: " . $e->getMessage());
        }
    }
}

// Handle review submission, editing, and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['email'])) {
    error_log("=== Review Submission Process Started ===");
    error_log("POST request received for review submission");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));
    
    // Handle new review submission
    $game_id = $_POST['game_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review_text = $_POST['review_text'] ?? null;

    error_log("Review submission attempt - Game ID: $game_id, Rating: $rating, Review Text Length: " . strlen($review_text));
    
    if ($game_id && $rating && $review_text) {
        try {
            $account = new Account();
            error_log("Attempting to get user by email: " . $_SESSION['email']);
            
            if (!$account->getUserByEmail($_SESSION['email'])) {
                error_log("User not found for email: " . $_SESSION['email']);
                throw new Exception("User not found");
            }
            
            $user_id = $account->getId();
            error_log("User found, ID: " . $user_id);
            error_log("Calling addReview with params: game_id=" . $game_id . ", user_id=" . $user_id . ", rating=" . $rating);
            
            // Check if user has already reviewed this game
            $has_reviewed = $boardgame->hasUserReviewed($user_id, $game_id);
            error_log("User has already reviewed this game: " . ($has_reviewed ? "yes" : "no"));
            
            if ($has_reviewed) {
                throw new Exception("You have already reviewed this game");
            }
            
            $result = $boardgame->addReview($game_id, $user_id, $rating, $review_text);
            error_log("addReview result: " . ($result ? "success" : "failed"));
            
            if (!$result) {
                throw new Exception("Failed to add review");
            }

            error_log("Review added successfully, redirecting...");
            // Redirect to the same page to show the new review
            header("Location: detail.php?id=" . $game_id);
            exit;
        } catch (Exception $e) {
            error_log("Error in review submission: " . $e->getMessage());
            $error = "Error submitting review: " . $e->getMessage();
        }
    } else {
        error_log("Missing required fields for review submission");
        error_log("Game ID: " . ($game_id ? "present" : "missing"));
        error_log("Rating: " . ($rating ? "present" : "missing"));
        error_log("Review Text: " . ($review_text ? "present" : "missing"));
        $error = "Please fill in all fields";
    }
    error_log("=== Review Submission Process Ended ===");
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
    'is_admin' => isAdmin(),
    'reviews' => $reviews,
    'error' => $error,
    'session_email' => $_SESSION['email'] ?? null,
    'session_username' => $_SESSION['username'] ?? null,
    'has_reviewed' => $has_reviewed,
    'is_favorited' => $is_favorited,
    'similar_games' => $similarGames
]);
?>