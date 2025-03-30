<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';

// Classes used in this page
use Adam\AwPhpProject\App;

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

// Loading the twig template
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$template = $twig->load('contact.twig');

// Render the output
echo $template->render([
    'website_name' => $site_name,
    'errors' => $form_errors,
    'success' => $form_success,
    'loggedin' => $isauthenticated,
    'current_page' => 'contact'
]);
?>

