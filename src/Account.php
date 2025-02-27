<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;

class Account extends Database {
    public function __construct()
    {
        try {
            $db = new Database();
            if (!$db) {
                throw new Exception("No database available.");
            }
            else {
                $this -> connection = $db -> connection;
            }
        }
        catch (Exception $exc) {
            exit($exc->getMessage());
        }
    }

    public function create($email, $password)
    {
        // Perform query to create an account with email and password
    }

    public function update()
    {

    }

    public function getAccount()
    {

    }

    public 
}
?>