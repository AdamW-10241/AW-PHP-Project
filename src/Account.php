<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;

class Account extends Database {
    public function __construct()
    {
        parent::__construct();
    }

    public function create($email, $password)
    {
        // Perform query to create an account with email and password
        $query = "INSERT INTO Account (
            email,
            password,
            reset,
            active,
            created
            VALUES (?,?,?,TRUE,NOW(),)"
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