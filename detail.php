<?php
require_once 'vendor/autoload.php';
// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\BoardGame;

if ($_SERVER["REQUEST_METHOD"] != "GET" && isset($_GET["id"])) {
    // Redirect to home page
    header("location: index.php");
}

// Create app from App class
$app = new App();
$boardgame = new BoardGame();
$detail = array();

if ( $_GET['id'] ) {
    $detail = $boardgame -> getDetail( $_GET['id'] );
}

$page_title = "Detail for " . $detail['title'];

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'detail.twig' );

// Render the output
echo $template -> render( [
    'detail' => $detail
] );
?>