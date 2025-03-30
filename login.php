<?php 
// Start session
session_start();
require_once 'vendor/autoload.php';
require_once 'session_helper.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Adam\AwPhpProject\Account;

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

// Initialize variables
$data = [
    'loggedin' => isLoggedIn(),
    'error' => null
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $account = new Account();
        $account->login($email, $password);
        
        // Set session and redirect
        $_SESSION['email'] = $email;
        header('Location: /index.php');
        exit();
    } catch (Exception $e) {
        $data['error'] = $e->getMessage();
    }
}

// Render template
echo $twig->render('login.twig', $data);
?>