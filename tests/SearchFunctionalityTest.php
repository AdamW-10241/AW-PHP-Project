<?php

use PHPUnit\Framework\TestCase;

class SearchFunctionalityTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Connect to the database
        $this->pdo = new PDO(
            "mysql:host=db;dbname=boardgames_db;charset=utf8mb4",
            "mariadb",
            "mariadb",
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
        // Insert test games
        $games = [
            [
                'title' => 'Echoes of Time',
                'description' => 'A strategic board game about time travel',
                'player_range' => '2-4',
                'age_range' => '12+',
                'min_playtime' => 60,
                'max_playtime' => 120,
                'min_price' => 29.99,
                'max_price' => 49.99,
                'image' => 'echoes_of_time.jpg'
            ],
            [
                'title' => 'Brass: Birmingham',
                'description' => 'An economic strategy game set during the industrial revolution',
                'player_range' => '2-4',
                'age_range' => '14+',
                'min_playtime' => 90,
                'max_playtime' => 180,
                'min_price' => 39.99,
                'max_price' => 59.99,
                'image' => 'brass_birmingham.jpg'
            ]
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO BoardGame (title, description, player_range, age_range, min_playtime, max_playtime, min_price, max_price, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($games as $game) {
            $stmt->execute([
                $game['title'],
                $game['description'],
                $game['player_range'],
                $game['age_range'],
                $game['min_playtime'],
                $game['max_playtime'],
                $game['min_price'],
                $game['max_price'],
                $game['image']
            ]);
        }

        // Log inserted data
        fwrite(STDERR, "\nInserted test data:\n");
        foreach ($games as $game) {
            fwrite(STDERR, "Title: {$game['title']}\n");
        }
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM BoardGame WHERE title IN ('Echoes of Time', 'Brass: Birmingham')");
    }

    private function logSearchDetails(string $originalTerm, string $cleanQuery, string $searchTerm, array $results): void
    {
        fwrite(STDERR, "\nSearch Details:\n");
        fwrite(STDERR, "Original term: '$originalTerm'\n");
        fwrite(STDERR, "Cleaned query: '$cleanQuery'\n");
        fwrite(STDERR, "Search term: '$searchTerm'\n");
        fwrite(STDERR, "Found results: " . count($results) . "\n");
        foreach ($results as $result) {
            fwrite(STDERR, "- $result\n");
        }
    }

    public function testSearchWithColon(): void
    {
        $searchTerm = "Brass:";
        $cleanQuery = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $searchTerm));
        $searchTerm = "%$cleanQuery%";
        
        $stmt = $this->pdo->prepare("
            SELECT title FROM BoardGame 
            WHERE LOWER(REPLACE(title, ':', '')) LIKE ?
        ");
        
        $stmt->execute([$searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->logSearchDetails("Brass:", $cleanQuery, $searchTerm, $results);
        $this->assertContains('Brass: Birmingham', $results, "Search for 'Brass:' should find 'Brass: Birmingham'");
    }

    public function testSearchWithPartialTitle(): void
    {
        $searchTerm = "Echoes";
        $cleanQuery = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $searchTerm));
        $searchTerm = "%$cleanQuery%";
        
        $stmt = $this->pdo->prepare("
            SELECT title FROM BoardGame 
            WHERE LOWER(REPLACE(title, ':', '')) LIKE ?
        ");
        
        $stmt->execute([$searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->logSearchDetails("Echoes", $cleanQuery, $searchTerm, $results);
        $this->assertContains('Echoes of Time', $results, "Search for 'Echoes' should find 'Echoes of Time'");
    }

    public function testSearchWithMultipleWords(): void
    {
        $searchTerm = "Echoes of";
        $cleanQuery = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $searchTerm));
        $searchTerm = "%$cleanQuery%";
        
        $stmt = $this->pdo->prepare("
            SELECT title FROM BoardGame 
            WHERE LOWER(REPLACE(title, ':', '')) LIKE ?
        ");
        
        $stmt->execute([$searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->logSearchDetails("Echoes of", $cleanQuery, $searchTerm, $results);
        $this->assertContains('Echoes of Time', $results, "Search for 'Echoes of' should find 'Echoes of Time'");
    }

    public function testSearchWithFilters(): void
    {
        $searchTerm = "Echoes";
        $cleanQuery = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $searchTerm));
        $searchTerm = "%$cleanQuery%";
        
        $minPrice = 0;
        $maxPrice = 100;
        $minPlaytime = 0;
        $maxPlaytime = 240;
        
        $sql = "SELECT title FROM BoardGame WHERE 1=1";
        $params = [];
        
        // Add search term
        $sql .= " AND (
            LOWER(REPLACE(title, ':', '')) LIKE ? OR 
            LOWER(REPLACE(description, ':', '')) LIKE ?
        )";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        
        // Add price filter
        $sql .= " AND (
            (min_price <= ? AND max_price >= ?) OR
            (min_price >= ? AND min_price <= ?) OR
            (max_price >= ? AND max_price <= ?)
        )";
        $params[] = $maxPrice;
        $params[] = $minPrice;
        $params[] = $minPrice;
        $params[] = $maxPrice;
        $params[] = $minPrice;
        $params[] = $maxPrice;
        
        // Add playtime filter
        $sql .= " AND (
            (min_playtime <= ? AND max_playtime >= ?) OR
            (min_playtime >= ? AND min_playtime <= ?) OR
            (max_playtime >= ? AND max_playtime <= ?)
        )";
        $params[] = $maxPlaytime;
        $params[] = $minPlaytime;
        $params[] = $minPlaytime;
        $params[] = $maxPlaytime;
        $params[] = $minPlaytime;
        $params[] = $maxPlaytime;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->logSearchDetails("Echoes with filters", $cleanQuery, $searchTerm, $results);
        $this->assertContains('Echoes of Time', $results, "Search for 'Echoes' with filters should find 'Echoes of Time'");
    }
} 