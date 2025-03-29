<?php
require_once 'config.php';
//this php file is for testing only just to apply data swiftly
try {
    // Get database connection
    $conn = getDBConnection();
    
    // Read the SQL file
    $sql = file_get_contents('database.sql');
    
    // Split into individual statements
    $statements = array_filter(
        array_map(
            function($query) {
                return trim($query);
            },
            explode(';', $sql)
        )
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                if (!$conn->query($statement)) {
                    echo "Error executing statement: " . $conn->error . "\n";
                    echo "Statement was: " . $statement . "\n\n";
                } else {
                    echo "Successfully executed: " . substr($statement, 0, 50) . "...\n";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                echo "Statement was: " . $statement . "\n\n";
            }
        }
    }
    
    echo "\nDatabase schema import completed!\n";
    
    // Verify tables were created
    $result = $conn->query("SHOW TABLES");
    echo "\nCreated tables:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    
    // Verify sample data
    $result = $conn->query("SELECT COUNT(*) as count FROM games");
    $row = $result->fetch_assoc();
    echo "\nNumber of games in database: " . $row['count'] . "\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
} 