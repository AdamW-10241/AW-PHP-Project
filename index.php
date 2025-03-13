<?php
require_once 'vendor/autoload.php';
// Classes used in this stage
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Book;

// Create app from App class
$app = new App();
$site_name = $app -> site_name;

$book = new Book();
$items = $book -> get();

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'page.twig' );

// Render the output
echo $template -> render( [
    'title' => $page_title,
    'items' => $items
] );
?>

