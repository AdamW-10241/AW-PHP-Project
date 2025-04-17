<?php
require_once 'config.php';
require_once 'session_helper.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to rate reviews']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get the review ID and rating from the request
$review_id = $_POST['review_id'] ?? null;
$rating = $_POST['rating'] ?? null;

// Validate inputs
if (!$review_id || !$rating) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get the user's email from the session
    $email = $_SESSION['email'];

    // Get the user's ID
    $stmt = $pdo->prepare("SELECT id FROM Account WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    $user_id = $user['id'];

    // Check if the user has already rated this review
    $stmt = $pdo->prepare("SELECT rating FROM ReviewRating WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $existing_rating = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_rating) {
        // If the user is clicking the same rating again, remove their rating
        if ($existing_rating['rating'] == $rating) {
            $stmt = $pdo->prepare("DELETE FROM ReviewRating WHERE review_id = ? AND user_id = ?");
            $stmt->execute([$review_id, $user_id]);
            $new_rating = 0;
        } else {
            // Update the existing rating
            $stmt = $pdo->prepare("UPDATE ReviewRating SET rating = ? WHERE review_id = ? AND user_id = ?");
            $stmt->execute([$rating, $review_id, $user_id]);
            $new_rating = $rating;
        }
    } else {
        // Add a new rating
        $stmt = $pdo->prepare("INSERT INTO ReviewRating (review_id, user_id, rating) VALUES (?, ?, ?)");
        $stmt->execute([$review_id, $user_id, $rating]);
        $new_rating = $rating;
    }

    // Calculate the new total rating for the review
    $stmt = $pdo->prepare("SELECT SUM(rating) as total FROM ReviewRating WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_rating = $result['total'] ?? 0;

    // Update the review's total rating
    $stmt = $pdo->prepare("UPDATE Review SET rating = ? WHERE id = ?");
    $stmt->execute([$total_rating, $review_id]);

    echo json_encode([
        'success' => true,
        'new_rating' => $total_rating,
        'user_rating' => $new_rating
    ]);

} catch (PDOException $e) {
    error_log("Error rating review: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?> 