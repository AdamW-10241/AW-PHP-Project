<?php 
// Start session before any output
session_start();

require_once 'vendor/autoload.php';
require_once 'session_helper.php';

// Classes used in this page
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\SessionManager;

// Create app from App class
$app = new App();
$site_name = $app->site_name;

// Create data variables
$login_errors = [];
$login_success = false;

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect to home if already logged in
    header("location: /");
    exit();
}

// Checking for form submission via POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Store email in a variable
    $email = $_POST['email'];
    // Store password in a variable
    $password = $_POST['password'];
    // Create an instance of account class
    $account = new Account();
    // Call the login method in account
    $account->login($email, $password);
    if ($account->response['success'] == true) {
        // Login successful, set the session variable
        $_SESSION['email'] = $email;
        $login_success = true;
        // Redirect to home page after successful login
        header("location: /");
        exit();
    } else {
        // There are errors
        $login_errors['message'] = implode(" ", $account->response['errors']);
    }
}

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('login.twig');

// Render the output
echo $template->render([
    'website_name' => $site_name,
    'errors' => $login_errors,
    'success' => $login_success,
    'loggedin' => isLoggedIn()
]);
?>