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
        // Create the insert query
        $insert_query = "INSERT INTO 
        Account( email, password, reset, active, last_seen, created ) 
        VALUES ( ?, ?, ?, 1, ?, ? )
        ";
        // Check if email is valid
        if (Validator::validateEmail($email) == false) {
            // Email is not valid
            $this -> errors['email'] = "Email address is not valid.";
        }
        // Check if email is already in account database
        $email_query = "SELECT EXISTS
        (SELECT 1 FROM Account WHERE email = ?)
        ";
        // Create mysql prepared statement
        $email_statement = $this -> connection -> prepare($email_query);
        // Binding parameters to the query
        $email_statement -> bind_param( "s", $email );
        $email_statement -> execute();
        $email_statement -> bind_result( $email_exists );
        $email_statement -> fetch();
        $email_statement -> close();
        // Execute statement
        if ( $email_exists ) {
            $this -> errors['email_used'] = "Email address is already used.";
        }
        // Check if password exists
        if (Validator::validatePassword($password) == false) {
            // Password is not valid
            $this -> errors['password'] = "Password does not meet requirements.";
        }
        // If there are errors, return the response
        if (\count($this -> errors) > 0) {
            $this -> response['success'] = 0;
            $this -> response['errors'] = $this -> errors;
            return($this -> response);
        }
        // If there are no errors
        $reset = \md5(time().random_int( 0, 5000 ));
        $hashed_pass = \password_hash( $password, \PASSWORD_DEFAULT );
        $create_time = date('Y-m-d H:i:s', time() );
        // Create mysql prepared statement
        $insert_statement = $this -> connection -> prepare($insert_query);
        // Binding parameters to the query
        $insert_statement -> bind_param( "sssss", $email, $hashed_pass, $reset, $create_time, $create_time );
        // Execute statement
        if ($insert_statement -> execute()) {
            $this -> response['success'] = 1;
        }
        else {
            $this -> response['success'] = 0;
            $this -> errors['Failed to execute query.'];
        }
        $insert_statement -> close();
        // Return the final response
        return($this -> response);
    }

    public function update()
    {

    }

    public function getAccount()
    {

    }

    public function login($email, $password)
    {
        // Check if email is valid
        if (Validator::validateEmail($email) == false) {
            $this->errors['email'] = "Email address is not valid.";
        }

        // Check if email exists in database
        $email_query = "SELECT EXISTS
        (SELECT 1 FROM Account WHERE email = ?)
        ";
        $email_statement = $this->connection->prepare($email_query);
        $email_statement->bind_param("s", $email);
        $email_statement->execute();
        $email_statement->bind_result($email_exists);
        $email_statement->fetch();
        $email_statement->close();

        if (!$email_exists) {
            $this->errors['email_not_found'] = "Email address not found.";
        }

        // If there are errors, return the response
        if (count($this->errors) > 0) {
            $this->response['success'] = false;
            $this->response['errors'] = $this->errors;
            return $this->response;
        }

        // Get the hashed password for the email
        $password_query = "SELECT password FROM Account WHERE email = ?";
        $password_statement = $this->connection->prepare($password_query);
        $password_statement->bind_param("s", $email);
        $password_statement->execute();
        $password_statement->bind_result($hashed_password);
        $password_statement->fetch();
        $password_statement->close();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $this->response['success'] = true;
        } else {
            $this->response['success'] = false;
            $this->errors['password'] = "Invalid password.";
            $this->response['errors'] = $this->errors;
        }

        return $this->response;
    }
}
?>