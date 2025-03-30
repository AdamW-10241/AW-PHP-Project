<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;

class BoardGame extends Database {
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $get_query = "
            SELECT 
            BoardGame.id AS id,
            BoardGame.title AS title,
            BoardGame.tagline AS tagline,
            BoardGame.year AS year,
            BoardGame.description AS description,
            BoardGame.player_range AS player_range,
            BoardGame.age_range AS age_range,
            BoardGame.playtime_range AS playtime_range,
            BoardGame.image AS image,
            BoardGame.tags AS tags,
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
            GROUP BY BoardGame.id;
        ";
        $statement = $this->connection->prepare($get_query);
        $statement->execute();

        // Get the results
        $boardgames = array();
        $result = $statement->get_result();

        // Loop through the result to add to array
        while ($row = $result->fetch_assoc()) {
            array_push($boardgames, $row);
        }
        
        // Return the array of items
        return $boardgames;
    }

    public function getDetail($id) {
        $detail_query = "
            SELECT 
            BoardGame.id AS id,
            BoardGame.title AS title,
            BoardGame.tagline AS tagline,
            BoardGame.year AS year,
            BoardGame.description AS description,
            BoardGame.player_range AS player_range,
            BoardGame.age_range AS age_range,
            BoardGame.playtime_range AS playtime_range,
            BoardGame.image AS image,
            BoardGame.tags AS tags,
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
            WHERE BoardGame.visible = 1 AND BoardGame.id = ?
            GROUP BY BoardGame.id;
        ";
        $statement = $this->connection->prepare($detail_query);
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        $detail = $result->fetch_assoc();

        // Get reviews for this game
        if ($detail) {
            $reviews_query = "
                SELECT 
                    r.*,
                    u.email as user_email
                FROM reviews r
                JOIN Account u ON r.user_id = u.id
                WHERE r.game_id = ?
                ORDER BY r.created_at DESC
            ";
            $stmt = $this->connection->prepare($reviews_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $reviews_result = $stmt->get_result();
            $detail['reviews'] = [];
            while ($review = $reviews_result->fetch_assoc()) {
                $detail['reviews'][] = $review;
            }
        }

        return $detail;
    }
}
?>