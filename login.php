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
        $result = $account->login($email, $password);
        
        if ($result['success'] === true) {
            // Set session and redirect only if login was successful
            $_SESSION['email'] = $email;

            $user = new Account();
            $user -> getUserByEmail($_SESSION['email']);

            $_SESSION['username'] = $user -> getUsername();;
            header('Location: /index.php');
            exit();
        } else {
            // Handle login errors
            if (isset($result['errors'])) {
                $data['errors'] = array_values($result['errors']);
            } else {
                $data['errors'] = ['Invalid email or password.'];
            }
        }
    } catch (Exception $e) {
        $data['errors'] = [$e->getMessage()];
    }
}

// Render template
echo $twig->render('login.twig', $data);
?>