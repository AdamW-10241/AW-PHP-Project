<?php
// Start session and set headers before any output
session_start();
header('Content-Type: application/json');

// Debug logging
error_log("=== SESSION DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session contents: " . print_r($_SESSION, true));
error_log("Cookie contents: " . print_r($_COOKIE, true));
error_log("=== END SESSION DEBUG ===");

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
error_log("Input contents: " . print_r($input, true));

require_once 'config.php';
require_once 'src/Security.php';
require_once 'src/BoardGame.php';
require_once 'src/Account.php';

use Adam\AwPhpProject\Security;
use Adam\AwPhpProject\BoardGame;
use Adam\AwPhpProject\Account;

// Check if user is logged in - be more lenient with the check
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['email']) || isset($_SESSION['username']);
error_log("Is logged in check: " . ($isLoggedIn ? 'true' : 'false'));

if (!$isLoggedIn) {
    error_log("User not logged in - no session variables found");
    echo json_encode(['success' => false, 'error' => 'You must be logged in to rate reviews']);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if (!$userId) {
    error_log("No user ID found in session");
    echo json_encode(['success' => false, 'error' => 'User ID not found in session']);
    exit;
}

// Check if required parameters are present
if (!isset($input['review_id']) || !isset($input['rating'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

try {
    // Sanitize and validate inputs
    $reviewId = Security::validateInteger($input['review_id']);
    $rating = intval($input['rating']); // Convert to integer
    
    error_log("Rating value: " . $rating);
    error_log("Rating type: " . gettype($rating));
    error_log("User ID: " . $userId);
    
    // Validate rating value - allow both string and integer values
    if ($rating != 1 && $rating != -1) {
        error_log("Invalid rating value received: " . $rating);
        echo json_encode(['success' => false, 'error' => 'Invalid rating value: ' . $rating]);
        exit;
    }

    // Rate the review
    $boardGame = new BoardGame();
    $result = $boardGame->rateReview($reviewId, $userId, $rating);

    // Return success response with updated counts
    echo json_encode([
        'success' => true,
        'action' => $result['action'],
        'like_count' => $result['like_count'],
        'dislike_count' => $result['dislike_count']
    ]);

} catch (Exception $e) {
    error_log("Error rating review: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while rating the review']);
}
?> 