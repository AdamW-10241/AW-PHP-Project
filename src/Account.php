<?php
namespace Adam\AwPhpProject;

use \Exception;
use Adam\AwPhpProject\Database;
use Adam\AwPhpProject\Validator;

class Account extends Database {
    public $errors = [];
    public $response = [];
    public function __construct()
    {
        parent::__construct();
    }

    public function create($email, $password)
    {
        // Perform query to create an account with email and password
        $create_query = "INSERT INTO Account (
            email,
            password,
            reset,
            active,
            created)
            VALUES (?,?,?,1,NOW())
        )";
        if (Validator::validateEmail($email) == false) {
            // Email is not valid
            $this -> errors['email'] = "Email address is not valid.";
        }
        if (Validator::validatePassword($password) == false) {
            // Password is not valid
            $this -> errors['password'] = "Password does not meet requirements.";
        }
        // If there are errors, return the response
        if (count($this -> errors) > 0) {
            $this -> response['success'] = 0;
            $this -> response['errors'] = $this -> errors;
            return($this -> response);
        }
        // If there are no errors
        $reset = md5(time().random_int(0,5000));
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        // Create mysql preparedd statement
        $statement = $this -> connection -> prepare($create_query);
        // Binding parameters to the query
        $statement -> bind_param("sss", $email, $hashed, $reset);
        // Execute statement
        if ($statement -> execute()) {
            $this -> response['success'] = 1;
        }
        else {
            $this -> response['success'] = 0;
            $this -> errors['Failed to execute query.'];
        }
    }

    public function update()
    {

    }

    public function getAccount()
    {

    }
}
?>