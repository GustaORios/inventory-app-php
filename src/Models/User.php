<?php
namespace Src\Models;

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../Common/config.php';

        if (!isset($conn) || !($conn instanceof \mysqli)) {
            throw new \Exception("Database connection not available. Check config.php");
        }
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO users (Username, PasswordHash, Role, Email, IsActive, CreatedAt, UpdatedAt)
                VALUES (?, ?, ?, ?, TRUE, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $username = $data['username'];
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'];
        $email = $data['email'];

        $stmt->bind_param("ssss", $username, $passwordHash, $role, $email);

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $newUserId = $this->conn->insert_id;
        $stmt->close();

        return $newUserId;
    }
}
