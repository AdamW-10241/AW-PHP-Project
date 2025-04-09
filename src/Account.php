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

    public function create($email, $password, $username)
    {
        // Perform query to create an account with email and password
        // Create the insert query
        $insert_query = "INSERT INTO 
        Account( email, password, username, reset, active, last_seen, created ) 
        VALUES ( ?, ?, ?, ?, 1, ?, ? )
        ";
        // Check if email is valid
        if (Validator::validateEmail($email) == false) {
            // Email is not valid
            $this -> errors['email'] = "Email address is not valid.";
        }
        // Check if username is valid
        if (Validator::validateUsername($username) == false) {
            // Username is not valid
            $this -> errors['username'] = "Username is not valid.";
        }
        // Check if username already exists
        if ($this -> usernameExists($username)) {
            // Username exists
            $this -> errors['username_exists'] = "Username already in use.";
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
        $insert_statement -> bind_param( "ssssss", $email, $hashed_pass, $username, $reset, $create_time, $create_time );
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

    public function getUserByEmail($email) {
        $query = "SELECT * FROM Account WHERE email = ?";
        $statement = $this->connection->prepare($query);
        $statement->bind_param("s", $email);
        $statement->execute();
        $result = $statement->get_result();
        return $result->fetch_assoc();
    }

    public function getUsername() {
        $query = "SELECT username FROM Account WHERE email = ?";
        $statement = $this->connection->prepare($query);
        $statement->bind_param("s", $email);
        $statement->execute();
        $result = $statement->get_result();
        return $result->fetch_assoc();
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

    /**
     * Check if email exists
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM Account WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        return $row[0] > 0;
    }

    /**
     * Check if username exists
     * @param string $username Username to check
     * @return bool True if username exists
     */
    public function usernameExists($username) {
        $sql = "SELECT COUNT(*) FROM Account WHERE username = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        return $row[0] > 0;
    }

    /**
     * Verify user password
     * @param string $email User's email
     * @param string $password Password to verify
     * @return bool True if password is correct
     */
    public function verifyPassword($email, $password) {
        $sql = "SELECT password FROM Account WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return password_verify($password, $row['password']);
    }

    /**
     * Update user's email
     * @param string $old_email Current email
     * @param string $new_email New email
     * @return bool True if update successful
     */
    public function updateEmail($old_email, $new_email) {
        $sql = "UPDATE Account SET email = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ss", $new_email, $old_email);
        return $stmt->execute();
    }

    /**
     * Update user's password
     * @param string $email User's email
     * @param string $new_password New password
     * @return bool True if update successful
     */
    public function updatePassword($email, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE Account SET password = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $email);
        return $stmt->execute();
    }

    /**
     * Update user's username
     * @param string $email User's email
     * @param string $new_username New username
     * @return bool True if update successful
     */
    public function updateUsername($email, $new_username) {
        $sql = "UPDATE Account SET username = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ss", $new_username, $email);
        return $stmt->execute();
    }
}
?>