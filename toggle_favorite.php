<?php
// Suppress warnings
error_reporting(E_ERROR | E_PARSE);

require_once 'config.php';
require_once 'session_helper.php';
require_once 'src/BoardGame.php';
require_once 'src/Account.php';
require_once 'src/Security.php';

use Adam\AwPhpProject\Security;

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!isLoggedIn()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
    } else {
        header('Location: /login.php');
    }
    exit;
}

if (!isset($_POST['game_id']) || !isset($_POST['csrf_token'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    } else {
        header('Location: /games.php');
    }
    exit;
}

// Validate CSRF token
if (!Security::validateToken($_POST['csrf_token'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    } else {
        header('Location: /games.php?error=' . urlencode('Invalid CSRF token'));
    }
    exit;
}

try {
    $boardgame = new \Adam\AwPhpProject\BoardGame();
    $account = new \Adam\AwPhpProject\Account();
    
    // Get user ID
    $user = $account->getUserByEmail($_SESSION['email']);
    if (!$user) {
        throw new Exception("User not found");
    }

    // Sanitize input
    $game_id = Security::sanitizeInput($_POST['game_id']);
    if (!Security::validateInteger($game_id)) {
        throw new Exception("Invalid game ID");
    }

    $result = $boardgame->toggleFavorite($user['id'], $game_id);

    if ($result) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'action' => $result['action']]);
        } else {
            // Redirect back to the detail page
            header('Location: /detail.php?id=' . $game_id);
        }
    } else {
        throw new Exception("Failed to toggle favorite");
    }
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        header('Location: /detail.php?id=' . $_POST['game_id'] . '&error=' . urlencode($e->getMessage()));
    }
} 