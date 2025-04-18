<?php
// Suppress warnings
error_reporting(E_ERROR | E_PARSE);

require_once 'config.php';
require_once 'session_helper.php';
require_once 'src/BoardGame.php';
require_once 'src/Account.php';
require_once 'src/Security.php';

use Adam\AwPhpProject\Security;

// Debug session state before starting
error_log("=== Toggle Favorite Request Start ===");
error_log("Session ID before: " . (session_id() ?: "none"));
error_log("Session status before: " . session_status());

// Ensure session is started with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => false, // Set to false for local development
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
        'use_cookies' => true,
        'use_only_cookies' => true
    ]);
}

// Debug session state after starting
error_log("Session ID after: " . session_id());
error_log("Session status after: " . session_status());
error_log("Session data: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));
error_log("Cookie data: " . print_r($_COOKIE, true));

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

if (!isset($_POST['game_id'])) {
    error_log("Missing game_id parameter in toggle_favorite.php");
    error_log("POST data: " . print_r($_POST, true));
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Missing game_id parameter']);
    } else {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/games.php';
        // Remove any existing error parameter from the referrer
        $referrer = preg_replace('/\?error=.*$/', '', $referrer);
        header('Location: ' . $referrer . '?error=' . urlencode('Missing game_id parameter'));
    }
    exit;
}

try {
    $boardgame = new \Adam\AwPhpProject\BoardGame();
    $account = new \Adam\AwPhpProject\Account();
    
    // Sanitize and validate game_id
    $game_id = Security::sanitizeInput($_POST['game_id']);
    if (!Security::validateInteger($game_id)) {
        throw new Exception("Invalid game ID");
    }
    
    // Get user ID
    if (isLoggedIn() && isset($_SESSION['email'])) {
        try {
            if (!$account->getUserByEmail($_SESSION['email'])) {
                throw new Exception("User not found");
            }

            $result = $boardgame->toggleFavorite($account->getId(), $game_id);

            if ($result) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'action' => $result['action']]);
                } else {
                    $referrer = $_SERVER['HTTP_REFERER'] ?? '/games.php';
                    // Remove any existing error parameter from the referrer
                    $referrer = preg_replace('/\?error=.*$/', '', $referrer);
                    header('Location: ' . $referrer);
                }
            } else {
                throw new Exception("Failed to toggle favorite");
            }
        } catch (Exception $e) {
            error_log("Error in toggle_favorite.php: " . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            } else {
                $referrer = $_SERVER['HTTP_REFERER'] ?? '/games.php';
                // Remove any existing error parameter from the referrer
                $referrer = preg_replace('/\?error=.*$/', '', $referrer);
                header('Location: ' . $referrer . '?error=' . urlencode($e->getMessage()));
            }
        }
    }
} catch (Exception $e) {
    error_log("Error in toggle_favorite.php: " . $e->getMessage());
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/games.php';
        // Remove any existing error parameter from the referrer
        $referrer = preg_replace('/\?error=.*$/', '', $referrer);
        header('Location: ' . $referrer . '?error=' . urlencode($e->getMessage()));
    }
} 