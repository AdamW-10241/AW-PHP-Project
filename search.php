<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';

// Classes used in this page
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Game;

// Create app from App class
$app = new App();
$site_name = $app->site_name;

// Check if user is logged in
$isauthenticated = false;
if (isset($_SESSION['email'])) {
    $isauthenticated = true;
}

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Create Game instance
$game = new Game();

// Get search results
$search_results = [];
if (!empty($search_query)) {
    $search_results = $game->searchGames($search_query);
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('search.twig');

// Render the output
echo $template->render([
    'website_name' => $site_name,
    'loggedin' => $isauthenticated,
    'search_query' => $search_query,
    'search_results' => $search_results
]); 