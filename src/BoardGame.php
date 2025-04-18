<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;
use PDO;
use PDOException;

class BoardGame extends Database {
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $sql = "
            SELECT 
                BoardGame.*,
                GROUP_CONCAT(DISTINCT Publisher.name) as publishers,
                GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name)) as designers,
                GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name)) as artists,
                CONCAT(min_playtime, '-', max_playtime) as playtime_range
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
                BoardGame.min_playtime,
                BoardGame.max_playtime,
                BoardGame.min_price,
                BoardGame.max_price,
                BoardGame.image,
                BoardGame.tags,
                BoardGame.franchise,
                BoardGame.brand,
                BoardGame.genre,
                BoardGame.visible,
                BoardGame.created_at
            ORDER BY BoardGame.title ASC
        ";

        // Debug: Print the query
        error_log("Executing query: " . $sql);

        $statement = $this->connection->prepare($sql);
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
                BoardGame.*,
                GROUP_CONCAT(DISTINCT CONCAT(Publisher.name) ORDER BY Publisher.name SEPARATOR ', ') AS publishers,
                GROUP_CONCAT(DISTINCT CONCAT(Designer.first_name, ' ', Designer.last_name) ORDER BY Designer.last_name SEPARATOR ', ') AS designers,
                GROUP_CONCAT(DISTINCT CONCAT(Artist.first_name, ' ', Artist.last_name) ORDER BY Artist.last_name SEPARATOR ', ') AS artists,
                CONCAT(min_playtime, '-', max_playtime) as playtime_range
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
                BoardGame.min_playtime,
                BoardGame.max_playtime,
                BoardGame.min_price,
                BoardGame.max_price,
                BoardGame.image,
                BoardGame.tags,
                BoardGame.franchise,
                BoardGame.brand,
                BoardGame.genre,
                BoardGame.visible,
                BoardGame.created_at
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
                    r.id,
                    r.game_id,
                    r.user_id,
                    r.star_rating as rating,
                    r.comment,
                    r.created_at,
                    u.email as user_email,
                    u.username
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
                r.id,
                r.game_id,
                r.user_id,
                r.star_rating as rating,
                r.comment,
                r.created_at,
                u.email as email,
                u.username as username,
                g.title as game_title,
                COALESCE(SUM(rr.rating = 1), 0) as like_count,
                COALESCE(SUM(rr.rating = -1), 0) as dislike_count,
                COALESCE((
                    SELECT rating 
                    FROM ReviewRating 
                    WHERE review_id = r.id AND user_id = ?
                ), 0) as user_rating
            FROM Review r
            JOIN Account u ON r.user_id = u.id
            JOIN BoardGame g ON r.game_id = g.id
            LEFT JOIN ReviewRating rr ON r.id = rr.review_id
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ";
        
        // Get the user_id from session or use 0 if not logged in
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        $statement = $this->connection->prepare($reviews_query);
        $statement->bind_param("i", $user_id);
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

    public function addReview($game_id, $user_id, $star_rating, $comment) {
        error_log("Adding review - Game ID: " . $game_id . ", User ID: " . $user_id . ", Rating: " . $star_rating);
        
        try {
            // Start transaction
            $this->connection->begin_transaction();
            
            // Check if user already has a review for this game
            $check_query = "SELECT id FROM Review WHERE game_id = ? AND user_id = ?";
            $check_stmt = $this->connection->prepare($check_query);
            $check_stmt->bind_param("ii", $game_id, $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $this->connection->rollback();
                return false; // User already has a review for this game
            }
            
            // Insert new review
            $insert_query = "INSERT INTO Review (game_id, user_id, star_rating, comment) VALUES (?, ?, ?, ?)";
            $insert_stmt = $this->connection->prepare($insert_query);
            $insert_stmt->bind_param("iiis", $game_id, $user_id, $star_rating, $comment);
            
            if (!$insert_stmt->execute()) {
                $this->connection->rollback();
                error_log("Error executing insert query: " . $insert_stmt->error);
                return false;
            }
            
            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->connection->rollback();
            error_log("Error adding review: " . $e->getMessage());
            return false;
        }
    }

    public function updateReview($review_id, $user_id, $star_rating, $comment) {
        // Verify the review belongs to the user
        $check_query = "SELECT id FROM Review WHERE id = ? AND user_id = ?";
        $check_stmt = $this->connection->prepare($check_query);
        $check_stmt->bind_param("ii", $review_id, $user_id);
        $check_stmt->execute();
        if (!$check_stmt->get_result()->fetch_assoc()) {
            throw new Exception("You can only edit your own reviews");
        }

        // Build the update query dynamically based on provided values
        $updates = [];
        $params = [];
        $types = "";
        
        if ($star_rating !== null) {
            $updates[] = "star_rating = ?";
            $params[] = $star_rating;
            $types .= "i";
        }
        
        if ($comment !== null) {
            $updates[] = "comment = ?";
            $params[] = $comment;
            $types .= "s";
        }
        
        if (empty($updates)) {
            throw new Exception("No fields to update");
        }
        
        // Add the review_id and user_id to the parameters
        $params[] = $review_id;
        $params[] = $user_id;
        $types .= "ii";
        
        // Update the review
        $update_query = "UPDATE Review SET " . implode(", ", $updates) . " WHERE id = ? AND user_id = ?";
        $update_stmt = $this->connection->prepare($update_query);
        $update_stmt->bind_param($types, ...$params);
        $update_stmt->execute();
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
                r.id,
                r.game_id,
                r.user_id,
                r.star_rating as rating,
                r.comment,
                r.created_at,
                u.email as email,
                u.username
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

    public function toggleFavorite($userId, $gameId) {
        try {
            // Validate inputs
            if (!Security::validateInteger($userId) || !Security::validateInteger($gameId)) {
                throw new Exception("Invalid user ID or game ID");
            }

            // Sanitize inputs
            $userId = Security::sanitizeInput($userId);
            $gameId = Security::sanitizeInput($gameId);

            // Check if already favorited
            $check_query = "SELECT * FROM Favourite WHERE user_id = ? AND boardgame_id = ?";
            $check_stmt = $this->connection->prepare($check_query);
            $check_stmt->bind_param("ii", $userId, $gameId);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Remove from favorites
                $delete_query = "DELETE FROM Favourite WHERE user_id = ? AND boardgame_id = ?";
                $delete_stmt = $this->connection->prepare($delete_query);
                $delete_stmt->bind_param("ii", $userId, $gameId);
                $delete_stmt->execute();
                
                if ($delete_stmt->affected_rows === 0) {
                    throw new Exception("Failed to remove favorite");
                }
                
                return ['action' => 'removed'];
            } else {
                // Add to favorites
                $insert_query = "INSERT INTO Favourite (user_id, boardgame_id) VALUES (?, ?)";
                $insert_stmt = $this->connection->prepare($insert_query);
                $insert_stmt->bind_param("ii", $userId, $gameId);
                $insert_stmt->execute();
                
                if ($insert_stmt->affected_rows === 0) {
                    throw new Exception("Failed to add favorite");
                }
                
                return ['action' => 'added'];
            }
        } catch (Exception $e) {
            error_log("Error in toggleFavorite: " . $e->getMessage());
            throw $e;
        }
    }

    public function isFavorited($userId, $gameId) {
        try {
            // Validate inputs
            if (!Security::validateInteger($userId) || !Security::validateInteger($gameId)) {
                throw new Exception("Invalid user ID or game ID");
            }

            // Sanitize inputs
            $userId = Security::sanitizeInput($userId);
            $gameId = Security::sanitizeInput($gameId);

            $query = "SELECT * FROM Favourite WHERE user_id = ? AND boardgame_id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("ii", $userId, $gameId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error in isFavorited: " . $e->getMessage());
            return false;
        }
    }

    public function getSimilarGames($gameId, $limit = 3) {
        try {
            error_log("=== Starting getSimilarGames for gameId: $gameId ===");
            
            // First get the current game's details
            $currentGame = $this->getDetail($gameId);
            if (!$currentGame) {
                error_log("ERROR: No current game found for ID: " . $gameId);
                return [];
            }

            error_log("Current game details: " . print_r($currentGame, true));

            // Extract player range (e.g., "2-4" -> [2,4])
            $playerRange = explode('-', $currentGame['player_range'] ?? '0-0');
            $minPlayers = (int)($playerRange[0] ?? 0);
            $maxPlayers = (int)($playerRange[1] ?? 0);
            error_log("Player range: $minPlayers-$maxPlayers");

            // Extract playtime range from min_playtime and max_playtime
            $minPlaytime = (int)($currentGame['min_playtime'] ?? 0);
            $maxPlaytime = (int)($currentGame['max_playtime'] ?? 0);
            error_log("Playtime range: $minPlaytime-$maxPlaytime");

            // Extract age range (e.g., "8+" -> 8)
            $minAge = (int)str_replace('+', '', $currentGame['age_range'] ?? '0+');
            error_log("Age range: $minAge+");

            // Extract designers, publishers, and genre
            $designers = array_map('trim', explode(',', $currentGame['designers'] ?? ''));
            $publishers = array_map('trim', explode(',', $currentGame['publishers'] ?? ''));
            $genre = $currentGame['genre'] ?? '';
            
            error_log("Genre: $genre");
            error_log("Designers: " . implode(', ', $designers));
            error_log("Publishers: " . implode(', ', $publishers));
            
            // Get the first designer and publisher for matching
            $firstDesigner = $designers[0] ?? '';
            $firstPublisher = $publishers[0] ?? '';

            // Query that matches games with similar characteristics
            $query = "SELECT DISTINCT b.id, b.title, b.image, b.player_range, 
                            CONCAT(b.min_playtime, '-', b.max_playtime) as playtime_range, 
                            b.age_range, b.genre
                     FROM BoardGame b
                     INNER JOIN BoardGame_Designer bgd ON b.id = bgd.boardgame_id
                     INNER JOIN Designer d ON bgd.designer_id = d.designer_id
                     INNER JOIN BoardGame_Publisher bgp ON b.id = bgp.boardgame_id
                     INNER JOIN Publisher p ON bgp.publisher_id = p.publisher_id
                     WHERE b.id != ?
                     AND b.visible = 1
                     AND (
                         b.genre = ?
                         OR b.player_range LIKE ?
                         OR b.player_range LIKE ?
                         OR (b.min_playtime BETWEEN ? AND ?)
                         OR (b.max_playtime BETWEEN ? AND ?)
                         OR b.age_range LIKE ?
                         OR d.first_name LIKE ?
                         OR d.last_name LIKE ?
                         OR p.name LIKE ?
                     )
                     ORDER BY RAND()
                     LIMIT ?";

            error_log("SQL Query: " . $query);
            
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                error_log("ERROR: Failed to prepare statement: " . $this->connection->error);
                return [];
            }
            
            // Prepare the parameters
            $playerRange1 = "%$minPlayers-%";
            $playerRange2 = "%-$maxPlayers%";
            $ageRange = "%$minAge+%";
            $designerPattern = "%$firstDesigner%";
            $publisherPattern = "%$firstPublisher%";
            
            // Create separate variables for the second set of playtime parameters
            $minPlaytime2 = $minPlaytime;
            $maxPlaytime2 = $maxPlaytime;
            
            error_log("Parameters: gameId=$gameId, genre=$genre, playerRange1=$playerRange1, playerRange2=$playerRange2, minPlaytime=$minPlaytime, maxPlaytime=$maxPlaytime, minPlaytime2=$minPlaytime2, maxPlaytime2=$maxPlaytime2, ageRange=$ageRange, designerPattern=$designerPattern, publisherPattern=$publisherPattern, limit=$limit");
            
            // Bind parameters - exactly 13 parameters
            $stmt->bind_param("isssiiiiissii", 
                $gameId, 
                $genre,
                $playerRange1,
                $playerRange2,
                $minPlaytime,
                $maxPlaytime,
                $minPlaytime2,
                $maxPlaytime2,
                $ageRange,
                $designerPattern,
                $designerPattern,
                $publisherPattern,
                $limit
            );
            
            $executeResult = $stmt->execute();
            if (!$executeResult) {
                error_log("ERROR: Failed to execute query: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("ERROR: Failed to get result: " . $stmt->error);
                return [];
            }
            
            $results = [];
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            
            error_log("Found " . count($results) . " similar games");
            if (count($results) > 0) {
                error_log("Similar games details: " . print_r($results, true));
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("EXCEPTION in getSimilarGames: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }

    public function rateReview($reviewId, $userId, $rating) {
        try {
            // Validate inputs
            if (!Security::validateInteger($reviewId) || !Security::validateInteger($userId) || !Security::validateInteger($rating)) {
                throw new Exception("Invalid input parameters");
            }

            // Check if user has already rated this review
            $stmt = $this->connection->prepare("
                SELECT rating 
                FROM ReviewRating 
                WHERE review_id = ? AND user_id = ?
            ");
            $stmt->execute([$reviewId, $userId]);
            $existingRating = $stmt->fetchColumn();

            if ($existingRating === false) {
                // User hasn't rated this review yet - insert new rating
                $stmt = $this->connection->prepare("
                    INSERT INTO ReviewRating (review_id, user_id, rating)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$reviewId, $userId, $rating]);
            } else if ($existingRating == $rating) {
                // User is trying to rate the same way again - remove the rating
                $stmt = $this->connection->prepare("
                    DELETE FROM ReviewRating 
                    WHERE review_id = ? AND user_id = ?
                ");
                $stmt->execute([$reviewId, $userId]);
            } else {
                // User is changing their rating - update it
                $stmt = $this->connection->prepare("
                    UPDATE ReviewRating 
                    SET rating = ? 
                    WHERE review_id = ? AND user_id = ?
                ");
                $stmt->execute([$rating, $reviewId, $userId]);
            }

            // Get updated counts
            $stmt = $this->connection->prepare("
                SELECT 
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as like_count,
                    SUM(CASE WHEN rating = -1 THEN 1 ELSE 0 END) as dislike_count
                FROM ReviewRating 
                WHERE review_id = ?
            ");
            $stmt->execute([$reviewId]);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'action' => $existingRating === false ? 'added' : ($existingRating == $rating ? 'removed' : 'changed'),
                'like_count' => (int)$counts['like_count'],
                'dislike_count' => (int)$counts['dislike_count']
            ];

        } catch (Exception $e) {
            error_log("Error in rateReview: " . $e->getMessage());
            throw $e;
        }
    }
}
?>