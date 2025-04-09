<?php
namespace Adam\AwPhpProject;

class Validator {
    public static function validateEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function validatePassword($password) {
        if (strlen($password) >= 8) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function validateUsername($username) {
        // Check length of username
        if (strlen($username) >= 32) {
            return false;
        }
        // Check if username is not alphanumerirc
        else if ( ctype_alnum($username) == false ) {
            return false;
        }
        else {
            return true;
        }
    }
}
?>