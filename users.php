<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'src/Account.php';
require_once 'src/Security.php';

use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\Security;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Please login first');
    exit;
}

$account = new Account();
if (!$account->getUserById($_SESSION['user_id']) || !$account->isAdmin()) {
    header('Location: index.php?error=Access denied');
    exit;
}

// Handle user actions (create, delete, toggle active status, toggle admin status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateToken($_POST['csrf_token'] ?? '')) {
        header('Location: users.php?error=Invalid CSRF token');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);

    switch ($action) {
        case 'create':
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$username || !$password) {
                header('Location: users.php?error=Please fill in all fields');
                exit;
            }

            try {
                $result = $account->create($email, $password, $username);
                if ($result['success']) {
                    header('Location: users.php?success=User created successfully');
                } else {
                    header('Location: users.php?error=' . ($result['error'] ?? 'Failed to create user'));
                }
            } catch (Exception $e) {
                header('Location: users.php?error=' . $e->getMessage());
            }
            exit;

        case 'delete':
            if ($user_id && $account->delete($user_id)) {
                header('Location: users.php?success=User deleted successfully');
            } else {
                header('Location: users.php?error=Failed to delete user');
            }
            exit;

        case 'toggle_active':
            if ($user_id && $account->toggleActive($user_id)) {
                header('Location: users.php?success=User status updated successfully');
            } else {
                header('Location: users.php?error=Failed to update user status');
            }
            exit;

        case 'toggle_admin':
            if ($user_id && $account->toggleAdmin($user_id)) {
                header('Location: users.php?success=Admin status updated successfully');
            } else {
                header('Location: users.php?error=Failed to update admin status');
            }
            exit;
    }
}

// Get all users
$users = $account->getAllUsers();

// Render the template
echo $twig->render('users.twig', [
    'users' => $users,
    'loggedin' => true, // We know user is logged in since we checked above
    'is_admin' => true, // We know user is admin since we checked above
    'csrf_token' => Security::generateToken(),
    'error' => $_GET['error'] ?? null,
    'success' => $_GET['success'] ?? null
]); 