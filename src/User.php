<?php

namespace Adam\AwPhpProject;

class User extends Database {
    private $db;

    public function __construct() {
        parent::__construct();
        $this->db = $this->connection;
    }

    /**
     * Get user by email
     * @param string $email User's email
     * @return array|false User data or false if not found
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM Account WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Check if email exists
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM Account WHERE email = ?";
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $email);
        return $stmt->execute();
    }
} 