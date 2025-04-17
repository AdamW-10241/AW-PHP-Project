<?php
// Suppress warnings
error_reporting(E_ERROR | E_PARSE);

require_once 'config.php';
require_once 'session_helper.php';
require_once 'src/BoardGame.php';

// Set headers before any output
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if (!isset($_POST['game_id'])) {
    echo json_encode(['success' => false, 'error' => 'Game ID not provided']);
    exit;
}

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get user ID
    $user_stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
    $user_stmt->execute([$_SESSION['email']]);
    $user_id = $user_stmt->fetchColumn();

    if (!$user_id) {
        throw new Exception("User not found");
    }

    $game_id = $_POST['game_id'];

    // Check if already favorited
    $check_query = "SELECT id FROM Favourite WHERE user_id = ? AND boardgame_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$user_id, $game_id]);
    $is_favorited = $check_stmt->fetchColumn() !== false;

    if ($is_favorited) {
        // Remove from favorites
        $delete_query = "DELETE FROM Favourite WHERE user_id = ? AND boardgame_id = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->execute([$user_id, $game_id]);

        if ($delete_stmt->rowCount() === 0) {
            throw new Exception("Failed to remove favorite");
        }

        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to favorites
        $insert_query = "INSERT INTO Favourite (user_id, boardgame_id) VALUES (?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->execute([$user_id, $game_id]);

        if ($insert_stmt->rowCount() === 0) {
            throw new Exception("Failed to add favorite");
        }

        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 