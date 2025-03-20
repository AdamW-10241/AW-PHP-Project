<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;

class Book extends Database {
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $get_query = "
            SELECT 
            Book.id AS id,
            Book.title AS title,
            Book.tagline AS tagline,
            Book.year AS year,
            Book.image AS image,
            GROUP_CONCAT(CONCAT(Author.author_first, ' ', Author.author_last) SEPARATOR ', ') AS author
            FROM 
            `Book` 
            INNER JOIN Book_Author ON Book_Author.book_id = Book.id
            INNER JOIN Author ON Book_Author.author_id = Author.author_id
            WHERE Book.visible=1
            GROUP BY id;
        ";
        $statement = $this -> connection -> prepare($get_query);
        $statement -> execute();

        // Get the results
        $books = array();
        $result = $statement -> get_result();

        // Loop through the result to add to array
        while ( $row = $result -> fetch_assoc() ) {
            \array_push( $books, $row );
        }
        
        // Return the array of items
        return $books;
    }

    public function getDetail($id) {
        $detail_query = "
            SELECT 
            Book.id AS id,
            Book.title AS title,
            Book.tagline AS tagline,
            Book.year AS year,
            Book.image AS image,
            GROUP_CONCAT(CONCAT(Author.author_first, ' ', Author.author_last) SEPARATOR ', ') AS author
            FROM 
            `Book` 
            INNER JOIN Book_Author ON Book_Author.book_id = Book.id
            INNER JOIN Author ON Book_Author.author_id = Author.author_id
            WHERE Book.visible=1
            GROUP BY id;
        ";

        
    }
}
?>