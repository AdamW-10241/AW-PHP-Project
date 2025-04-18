<?php
namespace Adam\AwPhpProject;

class Security {
    private static $tokenName = 'csrf-token';
    
    public static function getTokenName() {
        return self::$tokenName;
    }
    
    public static function generateToken() {
        // Generate a simple token based on time and random bytes
        $token = base64_encode(time() . bin2hex(random_bytes(16)));
        error_log("Generated token: " . $token);
        return $token;
    }
    
    public static function validateToken($token) {
        error_log("Validating token: " . $token);
        
        if (empty($token)) {
            error_log("Token is empty");
            return false;
        }
        
        // Decode the token
        $decoded = base64_decode($token);
        if ($decoded === false) {
            error_log("Token is not valid base64");
            return false;
        }
        
        // Extract timestamp
        $timestamp = substr($decoded, 0, 10);
        if (!is_numeric($timestamp)) {
            error_log("Invalid timestamp in token");
            return false;
        }
        
        // Check if token is not too old (5 minutes)
        if (time() - $timestamp > 300) {
            error_log("Token is too old");
            return false;
        }
        
        error_log("Token is valid");
        return true;
    }
    
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeOutput($output) {
        if (is_array($output)) {
            return array_map([self::class, 'sanitizeOutput'], $output);
        }
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validateInteger($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    public static function validateString($value, $minLength = 1, $maxLength = 255) {
        $value = trim($value);
        $length = mb_strlen($value, 'UTF-8');
        return $length >= $minLength && $length <= $maxLength;
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
} 