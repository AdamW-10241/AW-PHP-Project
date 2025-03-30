<?php
// Import database schema and sample data
require_once 'vendor/autoload.php';

// Connect to database
$db = new PDO(
    "mysql:host=db;dbname=mariadb;charset=utf8mb4",
    "mariadb",
    "mariadb",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Read schema file
$schema = file_get_contents('database.sql');

// Split into statements
$statements = array_filter(
    array_map('trim', 
        explode(';', $schema)
    )
);

// Execute schema statements
foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $db->exec($statement);
        } catch (PDOException $e) {
            echo "Error executing statement: " . $e->getMessage() . "\n";
        }
    }
}

// Verify tables
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Created " . count($tables) . " tables\n";

// Verify sample data
$counts = [
    'BoardGame' => $db->query("SELECT COUNT(*) FROM BoardGame")->fetchColumn(),
    'Publisher' => $db->query("SELECT COUNT(*) FROM Publisher")->fetchColumn(),
    'Designer' => $db->query("SELECT COUNT(*) FROM Designer")->fetchColumn(),
    'Artist' => $db->query("SELECT COUNT(*) FROM Artist")->fetchColumn()
];

echo "\nSample data imported:\n";
foreach ($counts as $table => $count) {
    echo "$table: $count records\n";
} 