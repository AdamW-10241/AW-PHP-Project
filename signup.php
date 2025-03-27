<?php 
require_once 'vendor/autoload.php';

// Classes used in this page
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\SessionManager;

// Create app from App class
$app = new App();
$site_name = $app -> site_name;
// create data variables
$signup_errors = [];

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
    if ($account -> response['success'] == true) {
        // Account has been created, set the session variable
        $_SESSION['email'] = $email;
    }
    else {
        // There are errors
        $signup_errors['message'] = implode(",", $account -> response['errors']);
    }
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment( $loader );
$template = $twig -> load( 'signup.twig' );

// Render the ouput
echo $template -> render( [ 
    'website_name' => $site_name,
    'errors' => $signup_errors
] );
?>