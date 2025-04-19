<?php
session_start();
require_once 'vendor/autoload.php';

// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\BoardGame;
use Adam\AwPhpProject\Security;

// Create app from App class
$app = new App();

// Checking if the user is logged in
$isauthenticated = false;
if (isset($_SESSION['email'])) {
    $isauthenticated = true;
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('user_profile.twig');

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    header('Location: /community.php');
    exit;
}

$account = new Account();
$boardGame = new BoardGame();

// Get user details
$user = $account->getUserById($userId);

if (!$user) {
    header('Location: /community.php');
    exit;
}

// Get user's favorite games
$favorites = $boardGame->getUserFavorites($userId);

// Get matching favorites if user is logged in
$matchingFavorites = [];
if ($isauthenticated && isset($_SESSION['user_id'])) {
    $loggedInUserId = $_SESSION['user_id'];
    if ($loggedInUserId != $userId) { // Only show matches if viewing someone else's profile
        $loggedInUserFavorites = $boardGame->getUserFavorites($loggedInUserId);
        $matchingFavorites = array_intersect_assoc(
            array_column($favorites, 'id'),
            array_column($loggedInUserFavorites, 'id')
        );
        
        // Get full game details for matching favorites
        if (!empty($matchingFavorites)) {
            $matchingFavorites = array_map(function($gameId) use ($boardGame) {
                return $boardGame->getDetail($gameId);
            }, $matchingFavorites);
        }
    }
}

// Render the output
echo $template->render([
    'title' => $user['username'] . "'s Profile",
    'user' => $user,
    'favorites' => $favorites,
    'matchingFavorites' => $matchingFavorites,
    'loggedin' => $isauthenticated,
    'session_email' => $_SESSION['email'],
    'csrf_token' => Security::generateToken(),
    'current_page' => 'profile'
]); 