<?php
require_once 'vendor/autoload.php';

// Classes used in this stage
use Adam\AwPhpProject\App;

// Create app from App class
$app = new App();
$site_name = $app -> site_name;

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'about.twig' );

// Render the output
echo $template -> render( [
    'site_name' => $site_name
] );
?>

