<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'vendor/autoload.php';
require_once 'src/Security.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Adam\AwPhpProject\Security;

// Ensure session is started with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // First get the user ID from email
    $user_stmt = $db->prepare("SELECT id FROM Account WHERE email = ?");
    $user_stmt->execute([$_SESSION['email']]);
    $user_id = $user_stmt->fetchColumn();

    if (!$user_id) {
        throw new Exception("User not found");
    }

    // Get user's favourites
    $stmt = $db->prepare("
        SELECT b.*, f.id as favourite_id
        FROM BoardGame b
        JOIN Favourite f ON b.id = f.boardgame_id
        WHERE f.user_id = ?
        ORDER BY f.position ASC
    ");
    $stmt->execute([$user_id]);
    $favourites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSRF token
    $csrf_token = Security::generateToken();
    error_log("Generated CSRF token in favourites.php: " . $csrf_token);
    error_log("Session ID in favourites.php: " . session_id());

    // Debug output
    error_log("User ID: " . $user_id);
    error_log("Number of favourites: " . count($favourites));
    foreach ($favourites as $favourite) {
        error_log("Favourite: " . $favourite['title'] . " (ID: " . $favourite['id'] . ")");
    }

    // Update image paths
    foreach ($favourites as &$favourite) {
        $favourite['image'] = 'assets/cover_images/board_' . $favourite['id'] . '.png';
    }

    echo $twig->render('favourites.twig', [
        'favourites' => $favourites,
        'loggedin' => isLoggedIn(),
        'is_admin' => isAdmin(),
        'csrf_token' => $csrf_token
    ]);
} catch (Exception $e) {
    error_log("Error in favourites.php: " . $e->getMessage());
    echo $twig->render('favourites.twig', [
        'error' => 'Error loading favourites: ' . $e->getMessage(),
        'favourites' => [],
        'loggedin' => isLoggedIn(),
        'is_admin' => isAdmin()
    ]);
}
?>

