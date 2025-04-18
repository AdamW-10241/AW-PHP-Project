<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'src/Account.php';
require_once 'src/Security.php';

use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\Security;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Check if user is logged in
$account = new Account();
$loggedin = isset($_SESSION['email']) && $account->getUserByEmail($_SESSION['email']);
$is_admin = $loggedin && $account->isAdmin();

// Get blog posts from database
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$query = "SELECT * FROM Blog_Post ORDER BY created_at DESC";
$result = $db->query($query);

$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

$db->close();

// Render the template
echo $twig->render('blog.twig', [
    'loggedin' => $loggedin,
    'is_admin' => $is_admin,
    'current_page' => 'blog',
    'posts' => $posts
]);
?>

