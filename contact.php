<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';

// Classes used in this page
use Adam\AwPhpProject\App;
use Adam\AwPhpProject\Security;

// Create app from App class
$app = new App();
$site_name = $app->site_name;

// Check if user is logged in
$isauthenticated = false;
if (isset($_SESSION['email'])) {
    $isauthenticated = true;
}

// Handle form submission
$form_errors = [];
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !Security::validateToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token";
        echo $twig->render('contact.twig', [
            'errors' => $errors,
            'success' => false
        ]);
        exit;
    }
    
    // Validate form data
    if (empty($_POST['name'])) {
        $form_errors['name'] = "Name is required";
    }
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $form_errors['email'] = "Valid email is required";
    }
    if (empty($_POST['subject'])) {
        $form_errors['subject'] = "Subject is required";
    }
    if (empty($_POST['message'])) {
        $form_errors['message'] = "Message is required";
    }

    // If no errors, show success message
    if (empty($form_errors)) {
        $form_success = true;
    }
}

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Render the template
echo $twig->render('contact.twig', [
    'loggedin' => $isauthenticated,
    'is_admin' => isAdmin(),
    'current_page' => 'contact',
    'form_errors' => $form_errors,
    'form_success' => $form_success
]);
?>

