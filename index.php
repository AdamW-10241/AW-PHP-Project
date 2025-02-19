<?php
require_once 'vendor/autoload.php';
// Testing phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'page.twig' );
// Render the output
echo $template -> render( [] );
?>

