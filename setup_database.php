<?php
require_once 'vendor/autoload.php';

use Adam\AwPhpProject\Account;
use Dotenv\Dotenv;

try {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // Create account instance which will establish database connection
    $account = new Account();
    
    // Drop existing tables in reverse order of dependencies
    $drop_tables = [
        'BoardGame_Artist',
        'BoardGame_Designer',
        'BoardGame_Publisher',
        'Artist',
        'Designer',
        'Publisher',
        'BoardGame',
        'Account'
    ];
    
    foreach ($drop_tables as $table) {
        $account->connection->query("DROP TABLE IF EXISTS $table");
    }
    
    // Read and execute schema SQL file
    $schema_sql = file_get_contents('database.sql');
    
    // Split SQL into individual statements
    $schema_statements = array_filter(array_map('trim', explode(';', $schema_sql)));
    
    // Execute each schema statement
    foreach ($schema_statements as $statement) {
        if (!empty($statement)) {
            if (!$account->connection->query($statement)) {
                throw new Exception("Error executing schema SQL: " . $account->connection->error);
            }
        }
    }
    
    // Read and execute sample data SQL file
    $sample_sql = file_get_contents('sample_data.sql');
    
    // Split SQL into individual statements
    $sample_statements = array_filter(array_map('trim', explode(';', $sample_sql)));
    
    // Execute each sample data statement
    foreach ($sample_statements as $statement) {
        if (!empty($statement)) {
            if (!$account->connection->query($statement)) {
                throw new Exception("Error executing sample data SQL: " . $account->connection->error);
            }
        }
    }
    
    echo "Database tables and sample data created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 