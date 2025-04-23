<?php 
// Start session
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'src/Account.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Adam\AwPhpProject\Account;

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

// Redirect if already logged in
if (isset($_SESSION['email'])) {
    header('Location: /index.php');
    exit();
}

// Initialize variables
$data = [
    'errors' => [],
    'success' => false,
    'email' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $data['email'] = $email; // Preserve email in form

    try {
        $account = new Account();
        
        // Check if email is empty
        if (empty($email)) {
            $data['errors'][] = "Email is required";
        }
        
        // Check if password is empty
        if (empty($password)) {
            $data['errors'][] = "Password is required";
        }
        
        // Only proceed if we have both email and password
        if (empty($data['errors'])) {
            if ($account->getUserByEmail($email)) {
                // Check if user is active
                if (!$account->isActive()) {
                    $data['errors'][] = 'Your account has been deactivated. Please contact an administrator.';
                } else if (!password_verify($password, $account->getPassword())) {
                    $data['errors'][] = "Invalid email or password";
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $account->getId();
                    $_SESSION['username'] = $account->getUsername();
                    $_SESSION['email'] = $account->getEmail();
                    $_SESSION['logged_in'] = true;
                    
                    // Debug log
                    error_log("Login successful. Session data: " . print_r($_SESSION, true));
                    
                    header("Location: index.php");
                    exit();
                }
            } else {
                $data['errors'][] = "Invalid email or password";
            }
        }
    } catch (Exception $e) {
        $data['errors'][] = "An error occurred. Please try again later.";
        error_log("Login error: " . $e->getMessage());
    }
}

// Render template
echo $twig->render('login.twig', $data);
?>