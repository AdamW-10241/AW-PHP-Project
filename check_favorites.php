<?php
require_once 'config.php';

$email = $argv[1] ?? null;
if (!$email) {
    die("Please provide an email as argument\n");
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
    $user_stmt->execute([$email]);
    $user_id = $user_stmt->fetchColumn();

    if (!$user_id) {
        die("User not found\n");
    }

    // Check Favourite table
    $favorite_stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM Favourite 
        WHERE user_id = ?
    ");
    $favorite_stmt->execute([$user_id]);
    $favorite_count = $favorite_stmt->fetchColumn();

    echo "User ID: " . $user_id . "\n";
    echo "Number of favorites: " . $favorite_count . "\n";

    // If there are favorites, show them
    if ($favorite_count > 0) {
        $favorites_stmt = $db->prepare("
            SELECT b.title, f.boardgame_id
            FROM Favourite f
            JOIN BoardGame b ON f.boardgame_id = b.id
            WHERE f.user_id = ?
        ");
        $favorites_stmt->execute([$user_id]);
        $favorites = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nFavorites:\n";
        foreach ($favorites as $favorite) {
            echo "- " . $favorite['title'] . " (ID: " . $favorite['boardgame_id'] . ")\n";
        }
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
} 