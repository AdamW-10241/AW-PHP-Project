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
            GROUP_CONCAT(DISTINCT CONCAT(Publisher.name) ORDER BY Publisher.name SEPARATOR ', ') AS publishers,
            GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name) ORDER BY Designer.last_name SEPARATOR ', ') AS designers,
            GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name) ORDER BY Artist.last_name SEPARATOR ', ') AS artists
            FROM BoardGame
            LEFT JOIN BoardGame_Publisher ON BoardGame.id = BoardGame_Publisher.boardgame_id
            LEFT JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.publisher_id
            LEFT JOIN BoardGame_Designer ON BoardGame.id = BoardGame_Designer.boardgame_id
            LEFT JOIN Designer ON BoardGame_Designer.designer_id = Designer.designer_id
            LEFT JOIN BoardGame_Artist ON BoardGame.id = BoardGame_Artist.boardgame_id
            LEFT JOIN Artist ON BoardGame_Artist.artist_id = Artist.artist_id
            WHERE BoardGame.visible = 1
            GROUP BY 
                BoardGame.id, 
                BoardGame.title,
                BoardGame.tagline,
                BoardGame.year,
                BoardGame.description,
                BoardGame.player_range,
                BoardGame.age_range,
                BoardGame.playtime_range,
                BoardGame.image,
                BoardGame.tags;
        ";

        // Debug: Print the query
        error_log("Executing query: " . $get_query);

        $statement = $this->connection->prepare($get_query);
        $statement->execute();

        // Get the results
        $boardgames = array();
        $result = $statement->get_result();

        // Debug: Print the number of rows
        error_log("Number of rows returned: " . $result->num_rows);

        // Loop through the result to add to array and debug each row
        while ($row = $result->fetch_assoc()) {
            error_log("Game ID: " . $row['id'] . " - Publishers: " . ($row['publishers'] ?? 'null') . 
                     " - Designers: " . ($row['designers'] ?? 'null') . 
                     " - Artists: " . ($row['artists'] ?? 'null'));
            array_push($boardgames, $row);
        }
        
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
            GROUP_CONCAT(DISTINCT CONCAT(Publisher.name) ORDER BY Publisher.name SEPARATOR ', ') AS publishers,
            GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name) ORDER BY Designer.last_name SEPARATOR ', ') AS designers,
            GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name) ORDER BY Artist.last_name SEPARATOR ', ') AS artists
            FROM BoardGame
            LEFT JOIN BoardGame_Publisher ON BoardGame.id = BoardGame_Publisher.boardgame_id
            LEFT JOIN Publisher ON BoardGame_Publisher.publisher_id = Publisher.publisher_id
            LEFT JOIN BoardGame_Designer ON BoardGame.id = BoardGame_Designer.boardgame_id
            LEFT JOIN Designer ON BoardGame_Designer.designer_id = Designer.designer_id
            LEFT JOIN BoardGame_Artist ON BoardGame.id = BoardGame_Artist.boardgame_id
            LEFT JOIN Artist ON BoardGame_Artist.artist_id = Artist.artist_id
            WHERE BoardGame.visible = 1 AND BoardGame.id = ?
            GROUP BY 
                BoardGame.id, 
                BoardGame.title,
                BoardGame.tagline,
                BoardGame.year,
                BoardGame.description,
                BoardGame.player_range,
                BoardGame.age_range,
                BoardGame.playtime_range,
                BoardGame.image,
                BoardGame.tags;
        ";

        // Debug: Print the query
        error_log("Executing detail query for ID " . $id);

        $statement = $this->connection->prepare($detail_query);
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        $detail = $result->fetch_assoc();

        // Debug: Print the detail results
        if ($detail) {
            error_log("Detail found for game ID: " . $id . 
                     " - Publishers: " . ($detail['publishers'] ?? 'null') . 
                     " - Designers: " . ($detail['designers'] ?? 'null') . 
                     " - Artists: " . ($detail['artists'] ?? 'null'));
        } else {
            error_log("No detail found for game ID: " . $id);
        }

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