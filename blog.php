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

$error = null;
$success = null;

// Handle blog post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    if (!isset($_POST['csrf_token']) || !Security::validateToken($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;
        $image_url = $_POST['image_url'] ?? null;

        if ($title && $content) {
            try {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                if ($db->connect_error) {
                    throw new Exception("Connection failed: " . $db->connect_error);
                }

                $stmt = $db->prepare("INSERT INTO Blog_Post (title, content, author_id, image_url) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssis", $title, $content, $_SESSION['user_id'], $image_url);
                
                if ($stmt->execute()) {
                    $success = "Blog post created successfully!";
                    header("Location: blog.php");
                    exit;
                } else {
                    throw new Exception("Error creating blog post: " . $stmt->error);
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    }
}

// Get blog posts from database
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$query = "SELECT bp.*, a.username as author_name 
          FROM Blog_Post bp 
          JOIN Account a ON bp.author_id = a.id 
          ORDER BY bp.created_at DESC";
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
    'posts' => $posts,
    'error' => $error,
    'success' => $success
]);
?>

