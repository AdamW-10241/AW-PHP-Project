<?php
namespace Adam\AwPhpProject;

class Feedback extends Database {
    private $id;
    private $name;
    private $email;
    private $subject;
    private $message;
    private $created_at;
    private $status;

    public function __construct() {
        parent::__construct();
    }

    public function create($name, $email, $subject, $message) {
        $sql = "INSERT INTO Feedback (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $this->id = $stmt->insert_id;
            return true;
        } else {
            error_log("Error executing statement: " . $stmt->error);
            return false;
        }
    }

    public function getAllFeedback() {
        $sql = "SELECT * FROM Feedback ORDER BY created_at DESC";
        $result = $this->connection->query($sql);
        
        if (!$result) {
            error_log("Error fetching feedback: " . $this->connection->error);
            return [];
        }

        $feedback = [];
        while ($row = $result->fetch_assoc()) {
            $feedback[] = $row;
        }
        return $feedback;
    }

    public function updateStatus($id, $status) {
        if (!in_array($status, ['new', 'read', 'replied'])) {
            return false;
        }

        $sql = "UPDATE Feedback SET status = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Error executing statement: " . $stmt->error);
            return false;
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getSubject() { return $this->subject; }
    public function getMessage() { return $this->message; }
    public function getCreatedAt() { return $this->created_at; }
    public function getStatus() { return $this->status; }
} 