<?php
// Start session before any output
session_start();

require_once 'vendor/autoload.php';

use Adam\AwPhpProject\App;
use Adam\AwPhpProject\SessionManager;

$app = new App();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to home page
header("location: /");
exit();
?>