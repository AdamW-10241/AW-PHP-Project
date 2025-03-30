<?php
session_start();
require_once 'vendor/autoload.php';

// Classes used in this stage
use Adam\AwPhpProject\App;

// Create app from App class
$app = new App();

// Checking if the user is logged in
$isauthenticated = false;
if ( isset( $_SESSION['email'] ) ) {
    $isauthenticated = true;
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader( 'templates' );
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'blog.twig' );

// Render the output
echo $template -> render( [
    'loggedin' => $isauthenticated,
    'current_page' => 'blog'
] );
?>

