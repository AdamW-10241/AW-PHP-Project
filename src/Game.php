<?php

namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;
use PDO;
use PDOException;

class Game extends Database {
    private $id;
    private $title;
    private $description;
    private $min_players;
    private $max_players;
    private $playtime;
    private $age;
    private $price;
    private $image_path;
    private $created;

    public function __construct() {
        parent::__construct();
    }

    public function create($title, $description, $min_players, $max_players, $playtime, $age, $price, $image_path)
    {
        // Remove 'assets/cover_images/' from the image path if it exists
        $image_path = str_replace('assets/cover_images/', '', $image_path);
        
        $query = "INSERT INTO BoardGame (title, description, player_range, age_range, min_playtime, max_playtime, min_price, max_price, image, visible, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        
        $statement = $this->connection->prepare($query);
        if (!$statement) {
            error_log("Failed to prepare statement: " . $this->connection->error);
            return false;
        }
        
        $player_range = "$min_players-$max_players";
        $age_range = "$age+";
        $min_playtime = (int)$playtime;
        $max_playtime = (int)$playtime;
        $min_price = $price;
        $max_price = $price;
        
        $statement->bind_param("ssssiidds", 
            $title, 
            $description, 
            $player_range,
            $age_range,
            $min_playtime,
            $max_playtime,
            $min_price,
            $max_price,
            $image_path
        );
        
        if (!$statement->execute()) {
            error_log("Failed to execute statement: " . $statement->error);
            return false;
        }
        
        return true;
    }

    public function getAllGames()
    {
        try {
            $query = "SELECT * FROM BoardGame ORDER BY created_at DESC";
            $statement = $this->connection->prepare($query);
            if (!$statement) {
                error_log("Failed to prepare statement: " . $this->connection->error);
                return [];
            }
            
            if (!$statement->execute()) {
                error_log("Failed to execute statement: " . $statement->error);
                return [];
            }
            
            $result = $statement->get_result();
            $games = [];
            while ($row = $result->fetch_assoc()) {
                // Split player_range into min_players and max_players
                $player_range = explode('-', $row['player_range']);
                $row['min_players'] = $player_range[0] ?? 1;
                $row['max_players'] = $player_range[1] ?? $player_range[0] ?? 1;
                
                // Use min_playtime as playtime
                $row['playtime'] = $row['min_playtime'];
                
                $games[] = $row;
            }
            return $games;
        } catch (Exception $e) {
            error_log("Error getting all games: " . $e->getMessage());
            return [];
        }
    }

    public function getGameById($id)
    {
        $query = "SELECT * FROM BoardGame WHERE id = ? AND visible = 1";
        $statement = $this->connection->prepare($query);
        if (!$statement) {
            error_log("Failed to prepare statement: " . $this->connection->error);
            return false;
        }
        
        $statement->bind_param("i", $id);
        if (!$statement->execute()) {
            error_log("Failed to execute statement: " . $statement->error);
            return false;
        }
        
        $result = $statement->get_result();
        $game = $result->fetch_assoc();
        
        if ($game) {
            $this->id = $game['id'];
            $this->title = $game['title'];
            $this->description = $game['description'];
            $this->min_players = explode('-', $game['player_range'])[0];
            $this->max_players = explode('-', $game['player_range'])[1];
            $this->playtime = $game['min_playtime'];
            $this->age = str_replace('+', '', $game['age_range']);
            $this->price = $game['min_price'];
            $this->image_path = $game['image'];
            $this->created = $game['created_at'];
            return true;
        }
        
        return false;
    }

    /**
     * Search games by name, description, or tags
     * @param string $query Search query
     * @return array Array of matching games
     */
    public function searchGames($query) {
        $query = "%$query%";
        $sql = "
            SELECT 
                BoardGame.id,
                BoardGame.title as name,
                BoardGame.tagline,
                BoardGame.description,
                BoardGame.player_range,
                BoardGame.age_range,
                BoardGame.playtime_range,
                BoardGame.image as image_url,
                BoardGame.tags,
                (SELECT GROUP_CONCAT(DISTINCT Publisher.name SEPARATOR ', ') 
                FROM BoardGame_Publisher 
                INNER JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.publisher_id
                WHERE BoardGame_Publisher.boardgame_id = BoardGame.id) AS publishers,
                (SELECT GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name) SEPARATOR ', ') 
                FROM BoardGame_Designer 
                INNER JOIN Designer ON BoardGame_Designer.designer_id = Designer.designer_id
                WHERE BoardGame_Designer.boardgame_id = BoardGame.id) AS designers,
                (SELECT GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name) SEPARATOR ', ') 
                FROM BoardGame_Artist 
                INNER JOIN Artist ON BoardGame_Artist.artist_id = Artist.artist_id
                WHERE BoardGame_Artist.boardgame_id = BoardGame.id) AS artists
            FROM BoardGame
            WHERE BoardGame.visible = 1
            AND (
                BoardGame.title LIKE ? 
                OR BoardGame.description LIKE ? 
                OR BoardGame.tagline LIKE ?
                OR BoardGame.tags LIKE ?
            )
            GROUP BY BoardGame.id
            ORDER BY BoardGame.title ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ssss", $query, $query, $query, $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $row['image_url'] = '/assets/cover_images/' . basename($row['image_url']);
            $games[] = $row;
        }
        return $games;
    }

    public function delete($id) {
        try {
            // First get the game to get the image path
            if (!$this->getGameById($id)) {
                return false;
            }

            // Delete the game from database
            $query = "DELETE FROM BoardGame WHERE id = ?";
            $statement = $this->connection->prepare($query);
            if (!$statement) {
                error_log("Failed to prepare statement: " . $this->connection->error);
                return false;
            }

            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                error_log("Failed to execute statement: " . $statement->error);
                return false;
            }

            // If there was an image, delete it
            if ($this->image_path && file_exists($this->image_path)) {
                unlink($this->image_path);
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting game: " . $e->getMessage());
            return false;
        }
    }

    public function getImagePath() {
        return $this->image_path;
    }
} 