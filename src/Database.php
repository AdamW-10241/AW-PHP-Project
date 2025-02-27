<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\App;

class Database extends App {
    protected $connection;
    protected function __construct()
    {
        try {
            if (
                $_ENV['DBHOST'] &&
                $_ENV['DBUSER'] &&
                $_ENV['DBPASSWORD'] &&
                $_ENV['DBNAME']
            ) {
                // Initialise connection
                try {
                    $this -> connection = mysqli_connect(
                        $_ENV['DBHOST'],
                        $_ENV['DBUSER'],
                        $_ENV['DBPASSWORD'],
                        $_ENV['DBNAME']
                    );
                    if (!$this->connection) {
                        throw new Exception("Database connection can not be created.");
                    }
                }
                catch (Exception $exc) {
                    exit($exc -> getMessage());
                }
            } 
            else {
                throw new Exception("Database credentials are not defined.");
            }
        } 
        catch (Exception $exc) {
            exit($exc -> getMessage());
        }
    }
}
?>