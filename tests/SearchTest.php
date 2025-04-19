<?php

use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Connect to the database using environment variables
        $host = 'db';  // Using the service name directly
        $dbname = 'boardgames_db';  // Using the correct database name
        $user = 'mariadb';
        $pass = 'mariadb';

        $this->pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Insert test data
        $this->insertTestData();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupTestData();
    }

    private function insertTestData(): void
    {
        // Insert test game
        $stmt = $this->pdo->prepare("
            INSERT INTO BoardGame (title, description, player_range, age_range, min_playtime, max_playtime, min_price, max_price, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Echoes of Time',
            'A strategic board game about time travel',
            '2-4',
            '12+',
            60,
            120,
            29.99,
            49.99,
            'echoes_of_time.jpg'
        ]);
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM BoardGame WHERE title = 'Echoes of Time'");
    }

    public function testPartialTitleSearch(): void
    {
        // Test searching for "Echoes"
        $searchTerm = "%echoes%";
        
        $stmt = $this->pdo->prepare("
            SELECT title FROM BoardGame 
            WHERE LOWER(title) LIKE ?
        ");
        
        $stmt->execute([$searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assertContains('Echoes of Time', $results, "Search for 'Echoes' should find 'Echoes of Time'");
    }

    public function testCaseInsensitiveSearch(): void
    {
        // Test searching with different cases
        $searchTerms = ['ECHOES', 'echoes', 'Echoes'];
        
        foreach ($searchTerms as $term) {
            $searchTerm = "%" . strtolower($term) . "%";
            
            $stmt = $this->pdo->prepare("
                SELECT title FROM BoardGame 
                WHERE LOWER(title) LIKE ?
            ");
            
            $stmt->execute([$searchTerm]);
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $this->assertContains('Echoes of Time', $results, "Search for '$term' should find 'Echoes of Time'");
        }
    }

    public function testSpecialCharacterSearch(): void
    {
        // Test searching with special characters
        $searchTerms = ['Echoes:', 'Echoes of', 'of Time'];
        
        foreach ($searchTerms as $term) {
            // Remove special characters but keep spaces, and convert to lowercase
            $cleanTerm = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $term));
            $searchTerm = "%" . $cleanTerm . "%";
            
            $stmt = $this->pdo->prepare("
                SELECT title FROM BoardGame 
                WHERE LOWER(REPLACE(title, ':', '')) LIKE ?
            ");
            
            $stmt->execute([$searchTerm]);
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $this->assertContains('Echoes of Time', $results, "Search for '$term' should find 'Echoes of Time'");
        }
    }
} 