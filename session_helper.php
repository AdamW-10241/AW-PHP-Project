<?php
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