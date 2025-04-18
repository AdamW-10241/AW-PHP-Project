<?php
// Start session before any output
session_start();

require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'src/Account.php';
require_once 'src/Security.php';
require_once 'src/Feedback.php';

// Classes used in this page
use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\Security;
use Adam\AwPhpProject\Feedback;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Check if user is logged in
$account = new Account();
$loggedin = isset($_SESSION['email']) && $account->getUserByEmail($_SESSION['email']);
$is_admin = $loggedin && $account->isAdmin();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Security::validateToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validate required fields
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        if (empty($subject)) {
            $errors[] = "Subject is required";
        }
        if (empty($message)) {
            $errors[] = "Message is required";
        }
        
        // If no errors, store feedback
        if (empty($errors)) {
            $feedback = new Feedback();
            if ($feedback->create($name, $email, $subject, $message)) {
                $success = "Your message has been sent successfully! We will get back to you soon.";
            } else {
                $errors[] = "Failed to send message. Please try again later.";
            }
        }
    }
}

// Render the template
echo $twig->render('contact.twig', [
    'errors' => $errors,
    'success' => $success,
    'loggedin' => $loggedin,
    'is_admin' => $is_admin,
    'current_page' => 'contact',
    'site_name' => 'Board Games'
]);
?>

