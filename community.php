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
$is_admin = false;
if (isset($_SESSION['email'])) {
    $isauthenticated = true;
    
    // Check if user is admin
    $account = new Account();
    if ($account->getUserByEmail($_SESSION['email'])) {
        $is_admin = $account->isAdmin();
    }
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('community.twig');

$boardGame = new BoardGame();

// Get all users
$users = $account->getAllUsers();

// Render the output
echo $template->render([
    'title' => 'Community',
    'users' => $users,
    'loggedin' => $isauthenticated,
    'is_admin' => $is_admin,
    'session_email' => $_SESSION['email'],
    'csrf_token' => Security::generateToken(),
    'current_page' => 'community'
]);
?>

