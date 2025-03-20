<?php
require_once 'vendor/autoload.php';
// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Book;

// Create app from App class
$app = new App();

$book = new Book();
$items = $book -> get();

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'page.twig' );

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    echo "detail for ";
    echo $_GET["id"];
}
else {
    // Redirect to home page
    header("location: index.php");
}
?>