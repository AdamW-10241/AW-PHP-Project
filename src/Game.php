<?php

namespace Adam\AwPhpProject;

class Game extends Database {
    private $db;

    public function __construct() {
        parent::__construct();
        $this->db = $this->connection;
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $query, $query]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Update image paths
        foreach ($results as &$game) {
            $game['image_url'] = '/assets/cover_images/' . basename($game['image_url']);
        }

        return $results;
    }
} 