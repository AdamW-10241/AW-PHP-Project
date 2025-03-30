<?php
// Import database schema and sample data
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Connect to the database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read the SQL file
    $sql = file_get_contents('database.sql');

    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    // Track which tables we've created
    $createdTables = [];

    // Execute each statement
    foreach ($statements as $statement) {
        if (empty($statement)) continue;

        // Check if this is a CREATE TABLE statement
        if (stripos($statement, 'CREATE TABLE') !== false) {
            // Extract table name
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                $createdTables[] = $tableName;
            }
        }

        try {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            // If the error is about table already existing, we can ignore it
            if ($e->getCode() == '42S01') {
                echo "Table already exists, skipping...\n";
                continue;
            }
            // If the error is about dropping a non-existent table, we can ignore it
            if ($e->getCode() == '1051') {
                echo "Table doesn't exist to drop, skipping...\n";
                continue;
            }
            // For other errors, we should report them
            echo "Error executing statement: " . $e->getMessage() . "\n";
        }
    }

    // Verify tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "\nCreated/Verified " . count($tables) . " tables\n";

    // Verify sample data
    $counts = [
        'BoardGame' => $pdo->query("SELECT COUNT(*) FROM BoardGame")->fetchColumn(),
        'Publisher' => $pdo->query("SELECT COUNT(*) FROM Publisher")->fetchColumn(),
        'Designer' => $pdo->query("SELECT COUNT(*) FROM Designer")->fetchColumn(),
        'Artist' => $pdo->query("SELECT COUNT(*) FROM Artist")->fetchColumn(),
        'News' => $pdo->query("SELECT COUNT(*) FROM News")->fetchColumn(),
        'Review' => $pdo->query("SELECT COUNT(*) FROM Review")->fetchColumn(),
        'Favourite' => $pdo->query("SELECT COUNT(*) FROM Favourite")->fetchColumn(),
        'Blog_Post' => $pdo->query("SELECT COUNT(*) FROM Blog_Post")->fetchColumn(),
        'Comment' => $pdo->query("SELECT COUNT(*) FROM Comment")->fetchColumn(),
        'Newsletter_Subscriber' => $pdo->query("SELECT COUNT(*) FROM Newsletter_Subscriber")->fetchColumn()
    ];

    echo "\nSample data imported:\n";
    foreach ($counts as $table => $count) {
        echo "$table: $count records\n";
    }

    echo "\nDatabase import completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 