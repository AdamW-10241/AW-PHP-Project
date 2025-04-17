<?php
require_once 'config.php';
require_once 'session_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['favorites']) || !is_array($data['favorites'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE Favourite SET position = ? WHERE user_id = ? AND boardgame_id = ?");
    
    foreach ($data['favorites'] as $position => $favorite) {
        $stmt->execute([
            $position,
            $_SESSION['user_id'],
            $favorite['id']
        ]);
    }

    $db->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 