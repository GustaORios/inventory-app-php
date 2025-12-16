<?php
namespace Src\Models;

use Src\Common\Logger;

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

    public function __destruct()
    {
        if ($this->conn instanceof \mysqli) {
            $this->conn->close();
        }
    }
    
    public function create(array $data)
    {
        //Validate if email already exists
        $sqlSelect = "SELECT userId FROM users WHERE email = ?";
        $stselect = $this->conn->prepare($sqlSelect);
        if (!$stselect) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }
        $stselect->bind_param("s", $data['email']);
        $stselect->execute();
        $user = $stselect->get_result();
        if ($user->num_rows > 0) {
            throw new \Exception("Email already registered.");
        }
        $stselect->close();

        // insert new user if email not found
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

        $newUserId = $this->conn->insert_id; // return new user id to controller
        $stmt->close();

        return $newUserId;
    }

    public function authenticate($email, $password){
        $sql = "SELECT userId, Username, PasswordHash, Role, Email FROM users WHERE Email = ? AND IsActive = TRUE";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return null; // User not found
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        if (password_verify($password, $user['PasswordHash'])) {
            unset($user['PasswordHash']); // Remove password hash before returning user data
            return $user;
        } else {
            return null; // Password does not match
            Logger::info("User@authenticate: Failed login attempt for email '{$email}'.");
        }

    }
}
