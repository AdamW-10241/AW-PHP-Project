<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you would typically save the email to a database
        // For now, we'll just redirect with a success message
        $_SESSION['message'] = 'Thank you for subscribing to our newsletter!';
    } else {
        $_SESSION['error'] = 'Please enter a valid email address.';
    }
}

// Redirect back to the homepage
header('Location: /index.php');
exit; 