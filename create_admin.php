<?php
require_once 'vendor/autoload.php';
require_once 'src/Account.php';

use Adam\AwPhpProject\Account;

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

// Get admin credentials from command line arguments
if ($argc < 4) {
    die("Usage: php create_admin.php <email> <password> <username>\n");
}

$email = $argv[1];
$password = $argv[2];
$username = $argv[3];

try {
    $account = new Account();
    $result = $account->create($email, $password, $username, true);
    
    if ($result['success'] === 1) {
        echo "Admin account created successfully!\n";
        echo "Email: $email\n";
        echo "Username: $username\n";
    } else {
        echo "Failed to create admin account:\n";
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $error) {
                echo "- $error\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 