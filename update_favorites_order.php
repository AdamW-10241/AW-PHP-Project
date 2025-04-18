<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'src/BoardGame.php';
require_once 'src/Account.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$isAjax) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data format']);
    exit;
}

try {
    $boardgame = new \Adam\AwPhpProject\BoardGame();
    $account = new \Adam\AwPhpProject\Account();
    
    // Get user ID
    if (isLoggedIn() && isset($_SESSION['email'])) {
        if (!$account->getUserByEmail($_SESSION['email'])) {
            throw new Exception("User not found");
        }
        
        $userId = $account->getId();
        
        // Update positions for each favorite
        foreach ($data as $item) {
            if (!isset($item['id']) || !isset($item['position'])) {
                continue;
            }
            
            $gameId = intval($item['id']);
            $position = intval($item['position']);
            
            // Update the position in the database
            $query = "UPDATE Favourite SET position = ? WHERE user_id = ? AND boardgame_id = ?";
            $stmt = $boardgame->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $boardgame->connection->error);
            }
            
            $stmt->bind_param("iii", $position, $userId, $gameId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update position: " . $stmt->error);
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("User not logged in");
    }
} catch (Exception $e) {
    error_log("Error in update_favorites_order.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 