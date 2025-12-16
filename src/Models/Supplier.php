<?php
namespace Src\Models;

class Supplier
{
    public $id;
    public $userId;
    public $name;
    public $email;
    public $address;
    public $phone;
    public $createdAt;
    public $updatedAt;

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


    public function getAll()
    {
        $sql = "SELECT SupplierId AS id, UserId AS userId, Name AS name, Email AS email, 
                       Address AS address, Phone AS phone, CreateAt AS createdAt, UpdateAt AS updatedAt
                FROM suppliers";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new \Exception("DB query failed: " . $this->conn->error);
        }

        $suppliers = [];
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }

        return $suppliers;
    }

    public function getById($id)
    {
        $sql = "SELECT SupplierId AS id, UserId AS userId, Name AS name, Email AS email, 
                       Address AS address, Phone AS phone, CreateAt AS createdAt, UpdateAt AS updatedAt
                FROM suppliers
                WHERE SupplierId = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $supplier = $result->fetch_assoc();

        $stmt->close();

        return $supplier;
    }

    // CREATE
    public function create(array $data)
    {
        $sql = "INSERT INTO suppliers (UserId, Name, Email, Address, Phone, CreateAt, UpdateAt)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $userId  = $data['userId'];
        $name    = $data['name'];
        $email   = $data['email'];
        $address = $data['address'];
        $phone   = $data['phone'];

        $stmt->bind_param("issss", $userId, $name, $email, $address, $phone);

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $newId = $this->conn->insert_id;

        $stmt->close();

        return $newId;
    }

    public function update($id, array $data)
    {
        $fields = [];
        $values = [];
        $types  = "";

        $allowed = ['userId', 'name', 'email', 'address', 'phone'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = ucfirst($field) . " = ?";
                $values[] = $data[$field];
                $types   .= ($field === 'userId') ? "i" : "s";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE suppliers SET " . implode(", ", $fields) . ", UpdateAt = NOW()
                WHERE SupplierId = ?";

        $stmt = $this->conn->prepare($sql);
        $types .= "i";
        $values[] = $id;

        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM suppliers WHERE SupplierId = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->affected_rows > 0;
    }
}
