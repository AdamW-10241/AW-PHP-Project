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
                FROM Review r
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

    public function getReviews() {
        $reviews_query = "
            SELECT 
                r.*,
                u.email as user_name,
                g.title as game_title
            FROM Review r
            JOIN Account u ON r.user_id = u.id
            JOIN BoardGame g ON r.game_id = g.id
            ORDER BY r.created_at DESC
        ";
        $statement = $this->connection->prepare($reviews_query);
        $statement->execute();
        $result = $statement->get_result();
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        return $reviews;
    }

    public function getGamesForReview() {
        $games_query = "
            SELECT id, title 
            FROM BoardGame 
            WHERE visible = 1 
            ORDER BY title ASC
        ";
        $statement = $this->connection->prepare($games_query);
        $statement->execute();
        $result = $statement->get_result();
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        return $games;
    }

    public function addReview($user_id, $game_id, $rating, $comment) {
        // Check if user already reviewed this game
        $check_query = "SELECT id FROM Review WHERE user_id = ? AND game_id = ?";
        $check_stmt = $this->connection->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $game_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->fetch_assoc()) {
            throw new Exception("You have already reviewed this game");
        }

        // Add the review
        $insert_query = "
            INSERT INTO Review (user_id, game_id, rating, comment, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ";
        $insert_stmt = $this->connection->prepare($insert_query);
        $insert_stmt->bind_param("iiis", $user_id, $game_id, $rating, $comment);
        return $insert_stmt->execute();
    }

    public function updateReview($review_id, $user_id, $rating, $comment) {
        // Verify the review belongs to the user
        $check_query = "SELECT id FROM Review WHERE id = ? AND user_id = ?";
        $check_stmt = $this->connection->prepare($check_query);
        $check_stmt->bind_param("ii", $review_id, $user_id);
        $check_stmt->execute();
        if (!$check_stmt->get_result()->fetch_assoc()) {
            throw new Exception("You can only edit your own reviews");
        }

        // Update the review
        $update_query = "
            UPDATE Review 
            SET rating = ?, comment = ?
            WHERE id = ? AND user_id = ?
        ";
        $update_stmt = $this->connection->prepare($update_query);
        $update_stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);
        return $update_stmt->execute();
    }

    public function deleteReview($review_id, $user_id) {
        // Verify the review belongs to the user
        $check_query = "SELECT id FROM Review WHERE id = ? AND user_id = ?";
        $check_stmt = $this->connection->prepare($check_query);
        $check_stmt->bind_param("ii", $review_id, $user_id);
        $check_stmt->execute();
        if (!$check_stmt->get_result()->fetch_assoc()) {
            throw new Exception("You can only delete your own reviews");
        }

        // Delete the review
        $delete_query = "DELETE FROM Review WHERE id = ? AND user_id = ?";
        $delete_stmt = $this->connection->prepare($delete_query);
        $delete_stmt->bind_param("ii", $review_id, $user_id);
        return $delete_stmt->execute();
    }

    public function getReviewsForGame($game_id) {
        $reviews_query = "
            SELECT 
                r.*,
                u.email as user_name
            FROM Review r
            JOIN Account u ON r.user_id = u.id
            WHERE r.game_id = ?
            ORDER BY r.created_at DESC
        ";
        $statement = $this->connection->prepare($reviews_query);
        $statement->bind_param("i", $game_id);
        $statement->execute();
        $result = $statement->get_result();
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        return $reviews;
    }

    public function hasUserReviewed($user_id, $game_id) {
        $check_query = "SELECT id FROM Review WHERE user_id = ? AND game_id = ?";
        $check_stmt = $this->connection->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $game_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        return $result->num_rows > 0;
    }
}
?>