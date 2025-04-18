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

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Render the output
echo $twig->render('blog.twig', [
    'loggedin' => $isauthenticated,
    'is_admin' => isAdmin(),
    'current_page' => 'blog'
]);
?>

