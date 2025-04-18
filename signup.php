<?php 
// Start session
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'session_helper.php';
require_once 'src/Account.php';

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
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !Security::validateToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token";
        echo $twig->render('signup.twig', ['errors' => $errors]);
        exit;
    }
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $username = $_POST['username'] ?? '';

    try {
        $account = new Account();
        $result = $account->create($email, $password, $username);
        
        if ($result['success'] === 1) {
            // Set session and redirect
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            header('Location: /index.php');
            exit();
        } else {
            // Handle errors from Account::create
            if (isset($result['errors'])) {
                $data['errors'] = array_values($result['errors']);
            } else {
                $data['errors'] = ['An error occurred during registration.'];
            }
        }
    } catch (Exception $e) {
        $data['errors'] = [$e->getMessage()];
    }
}

// Render template
echo $twig->render('signup.twig', [
    'errors' => $errors,
    'loggedin' => isLoggedIn(),
    'is_admin' => isAdmin()
]);
?>