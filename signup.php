<?php 
require_once 'vendor/autoload.php';

// Classes used in this page
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Account;

// Create app from App class
$app = new App();
$site_name = $app -> site_name;
// create data variables
$page_title = "Signup for an account";

// Checking for for submission via POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Store email in a variable
    $email = $_POST['email'];
    // Store password in a variable
    $password = $_POST['password'];
    // Create an instance of account class
    $account = new Account();
    // Call the create method in account
    $account -> create($email, $password);
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'signup.twig' );

// Render the ouput
echo $template -> render( [ 
    'title' => $page_title, 
    'website_name' => $site_name 
] );
?>