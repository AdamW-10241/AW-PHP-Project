<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';
// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\BoardGame;

// Create app from App class
$app = new App();
$boardgame = new BoardGame();
$items = $boardgame->get();

// Checking if the user is logged in
$isauthenticated = false;
if (isset($_SESSION['email'])) {
    $isauthenticated = true;
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('games.twig');

// Render the output
echo $template->render([
    'items' => $items,
    'loggedin' => $isauthenticated
]); 