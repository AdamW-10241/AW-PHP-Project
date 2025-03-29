<?php

namespace Adam\AwPhpProject;

class Game {
    private $db;

    public function __construct() {
        try {
            $this->db = new \PDO(
                "mysql:host=db;dbname=mariadb;charset=utf8mb4",
                "mariadb",
                "mariadb",
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Search games by name or description
     * @param string $query Search query
     * @return array Array of matching games
     */
    public function searchGames($query) {
        $query = "%$query%";
        $sql = "SELECT * FROM games WHERE name LIKE ? OR description LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
} 