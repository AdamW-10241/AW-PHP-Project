<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';
require_once 'session_helper.php';

// Classes used in this page
use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Security;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Create app from App class
$app = new App();
$site_name = $app->site_name;

// Check if user is logged in
$isauthenticated = false;
if (isset($_SESSION['email'])) {
    $isauthenticated = true;
} else {
    // Redirect to login if not authenticated
    header('Location: login.php');
    exit();
}

// Create Account instance
$account = new Account();

// Get account data
if (!$account->getUserByEmail($_SESSION['email'])) {
    header('Location: login.php?error=User not found');
    exit();
}

// Check if user is active
if (!$account->isActive()) {
    header('Location: login.php?error=Your account has been deactivated');
    exit();
}

// Initialize Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !Security::validateToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token";
        echo $twig->render('profile.twig', [
            'errors' => $errors,
            'success_message' => $success_message,
            'account' => $account
        ]);
        exit;
    }
    
    $action = $_POST['action'] ?? '';

    if ($action === 'update_username') {
        $new_username = trim($_POST['new_username'] ?? '');
        $confirm_username = trim($_POST['confirm_username'] ?? '');

        // Validate username
        if ($new_username !== $confirm_username) {
            $error_message = "Usernames do not match.";
        } elseif ($account->usernameExists($new_username)) {
            $error_message = "This username is already registered.";
        } else {
            // Update username
            if ($account->updateUsername($_SESSION['email'], $new_username)) {
                $_SESSION['username'] = $new_username;
                $success_message = "Username updated successfully.";
                $account->getUserByEmail($_SESSION['email']); // Refresh account data
            } else {
                $error_message = "Failed to update username. Please try again.";
            }
        }
    } elseif ($action === 'update_email') {
        $new_email = trim($_POST['new_email'] ?? '');
        $confirm_email = trim($_POST['confirm_email'] ?? '');

        // Validate email
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } elseif ($new_email !== $confirm_email) {
            $error_message = "Email addresses do not match.";
        } elseif ($account->emailExists($new_email)) {
            $error_message = "This email is already registered.";
        } else {
            // Update email
            if ($account->updateEmail($_SESSION['email'], $new_email)) {
                $_SESSION['email'] = $new_email;
                $success_message = "Email updated successfully.";
                $account->getUserByEmail($new_email); // Refresh account data with new email
            } else {
                $error_message = "Failed to update email. Please try again.";
            }
        }
    } elseif ($action === 'update_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate current password
        if (!$account->verifyPassword($_SESSION['email'], $current_password)) {
            $error_message = "Current password is incorrect.";
        } elseif (empty($new_password) || strlen($new_password) < 6) {
            $error_message = "New password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } else {
            // Update password
            if ($account->updatePassword($_SESSION['email'], $new_password)) {
                $success_message = "Password updated successfully.";
            } else {
                $error_message = "Failed to update password. Please try again.";
            }
        }
    }
}

// Render the profile template
echo $twig->render('profile.twig', [
    'account' => $account,
    'success_message' => $success_message,
    'error_message' => $error_message,
    'loggedin' => $isauthenticated,
    'is_admin' => $account->isAdmin(),
    'current_page' => 'profile'
]);