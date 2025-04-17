<?php
namespace Adam\AwPhpProject;

class Security {
    private static $tokenName = 'csrf_token';
    
    public static function generateToken() {
        if (empty($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$tokenName];
    }
    
    public static function validateToken($token) {
        if (empty($_SESSION[self::$tokenName]) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION[self::$tokenName], $token);
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