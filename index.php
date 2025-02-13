<?php
require_once 'vendor/autoload.php';
// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'page.twig' );
// Render the output
echo $template -> render( [] );
?>

