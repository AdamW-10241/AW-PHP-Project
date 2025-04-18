<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'src/Account.php';
require_once 'src/Security.php';
require_once 'src/Feedback.php';

use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\Security;
use Adam\AwPhpProject\Feedback;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Check if user is logged in and is admin
$account = new Account();
if (!isset($_SESSION['email']) || !$account->getUserByEmail($_SESSION['email']) || !$account->isAdmin()) {
    header('Location: /login.php');
    exit();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Security::validateToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token";
    } else {
        $feedback = new Feedback();
        $feedback_id = (int)($_POST['feedback_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if ($feedback_id <= 0) {
            $errors[] = "Invalid feedback ID";
        } elseif (!in_array($status, ['new', 'read', 'replied'])) {
            $errors[] = "Invalid status";
        } else {
            if ($feedback->updateStatus($feedback_id, $status)) {
                $success = "Feedback status updated successfully!";
            } else {
                $errors[] = "Failed to update feedback status";
            }
        }
    }
}

// Get all feedback
$feedback = new Feedback();
$all_feedback = $feedback->getAllFeedback();

// Render the template
echo $twig->render('feedback.twig', [
    'errors' => $errors,
    'success' => $success,
    'loggedin' => true,
    'is_admin' => true,
    'current_page' => 'feedback',
    'feedback' => $all_feedback
]); 