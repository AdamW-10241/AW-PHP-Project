<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\App;

class Database extends App {
    public $connection;
    protected function __construct()
    {
        try {
            if (
                $_ENV['DB_HOST'] &&
                $_ENV['DB_USER'] &&
                $_ENV['DB_PASS'] &&
                $_ENV['DB_NAME']
            ) {
                // Initialise connection
                try {
                    $this->connection = mysqli_connect(
                        $_ENV['DB_HOST'],
                        $_ENV['DB_USER'],
                        $_ENV['DB_PASS'],
                        $_ENV['DB_NAME']
                    );
                    if (!$this->connection) {
                        throw new Exception("Database connection can not be created.");
                    }
                }
                catch (Exception $exc) {
                    exit($exc->getMessage());
                }
            } 
            else {
                throw new Exception("Database credentials are not defined.");
            }
        } 
        catch (Exception $exc) {
            exit($exc->getMessage());
        }
    }
}
?>