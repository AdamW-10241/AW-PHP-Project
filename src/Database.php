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
                defined('DB_HOST') &&
                defined('DB_USER') &&
                defined('DB_PASS') &&
                defined('DB_NAME')
            ) {
                // Initialise connection
                try {
                    $this->connection = mysqli_connect(
                        DB_HOST,
                        DB_USER,
                        DB_PASS,
                        DB_NAME
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