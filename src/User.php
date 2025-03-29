<?php

namespace Adam\AwPhpProject;

class User {
    private $db;

    public function __construct() {
        try {
            $this->db = new \PDO(
                "mysql:host=db;dbname=mariadb;charset=utf8mb4",
                "mariadb",
                "mariadb",
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get user by email
     * @param string $email User's email
     * @return array|false User data or false if not found
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM Account WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Check if email exists
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM Account WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verify user password
     * @param string $email User's email
     * @param string $password Password to verify
     * @return bool True if password is correct
     */
    public function verifyPassword($email, $password) {
        $sql = "SELECT password FROM Account WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $hashed_password = $stmt->fetchColumn();
        return password_verify($password, $hashed_password);
    }

    /**
     * Update user's email
     * @param string $old_email Current email
     * @param string $new_email New email
     * @return bool True if update successful
     */
    public function updateEmail($old_email, $new_email) {
        $sql = "UPDATE Account SET email = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$new_email, $old_email]);
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
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$hashed_password, $email]);
    }
} 