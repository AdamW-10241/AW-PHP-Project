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
            BoardGame.image AS image,
            GROUP_CONCAT(CONCAT(Author.author_first, ' ', Author.author_last) SEPARATOR ', ') AS author
            FROM 
            `BoardGame` 
            INNER JOIN BoardGame_Author ON BoardGame_Author.boardgame_id = BoardGame.id
            INNER JOIN Author ON BoardGame_Author.author_id = Author.author_id
            WHERE BoardGame.visible=1
            GROUP BY id;
        ";
        $statement = $this -> connection -> prepare( $get_query );
        $statement -> execute();

        // Get the results
        $boardgames = array();
        $result = $statement -> get_result();

        // Loop through the result to add to array
        while ( $row = $result -> fetch_assoc() ) {
            \array_push( $boardgames, $row );
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
            BoardGame.isbn10 AS isbn10,
            BoardGame.isbn13 AS isbn13,
            BoardGame.pages as pages,
            BoardGame.summary as summary,
            BoardGame.tags as tags,
            BoardGame.image AS image,
            GROUP_CONCAT(CONCAT(Author.author_first, ' ', Author.author_last) SEPARATOR ', ') AS author
            FROM 
            `BoardGame` 
            INNER JOIN BoardGame_Author ON BoardGame_Author.boardgame_id = BoardGame.id
            INNER JOIN Author ON BoardGame_Author.author_id = Author.author_id
            WHERE BoardGame.visible=1 AND BoardGame.id = ?
            GROUP BY id;
        ";
        $statement = $this -> connection -> prepare( $detail_query );
        $statement -> bind_param( "i", $id );
        $statement -> execute();
        $boardgame_detail = array();
        $result = $statement -> get_result();
        $boardgame_detail = $result -> fetch_assoc();
        return $boardgame_detail;
    }
}
?>