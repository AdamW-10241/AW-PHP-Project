<?php
require_once __DIR__ . '/src/Account.php';

use Adam\AwPhpProject\Account;

function isLoggedIn() {
    return isset($_SESSION['email']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("location: /login.php");
        exit();
    }
}

function getLoggedInUser() {
    if (isLoggedIn()) {
        return $_SESSION['email'];
    }
    return null;
}

function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $account = new Account();
    if ($account->getUserByEmail($_SESSION['email'])) {
        return $account->isAdmin();
    }
    return false;
} 