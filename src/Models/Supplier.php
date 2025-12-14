<?php
namespace Src\Models;

class Supplier
{
    public $id;
    public $name;
    public $email;
    public $role;
    public $status;
    public $createdAt;
    public $updatedAt;

    private $conn;

    public function __construct()
    {
        require_once __DIR__ . '/../Common/config.php';

        if (!isset($conn) || !($conn instanceof \mysqli)) {
            throw new \Exception("Database connection not available. Check config.php");
        }

        $this->conn = $conn;
    }

    public function getAll()
    {
        $sql = "SELECT SupplierId AS id, Name AS name, Email AS email, Role AS role, Status AS status, CreateAt AS createdAt, UpdateAt AS updatedAt
                FROM Suppliers";

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
        $sql = "SELECT SupplierId AS id, Name AS name, Email AS email, Role AS role, Status AS status, CreateAt AS createdAt, UpdateAt AS updatedAt
                FROM Suppliers
                WHERE SupplierId = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $supplier = $result->fetch_assoc();

        $stmt->close();

        return $supplier ?: null;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO Suppliers (Name, Email, Role, Status, CreateAt, UpdateAt)
            VALUES (?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $name = $data['name'];
        $email = $data['email'];
        $role = $data['role'];
        $status = $data['status'];

        $stmt->bind_param("ssss", $name, $email, $role, $status);

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $newId = $this->conn->insert_id;

        $stmt->close();

        return $newId;
    }

    // UPDATE
    public function update($id, array $data)
    {
        $fields = [];
        $values = [];
        $types = "";

        $allowed = ['name', 'email', 'role', 'status'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = ucfirst($field) . " = ?";
                $values[] = $data[$field];
                $types .= "s";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE Suppliers SET " . implode(", ", $fields) . ", UpdateAt = NOW()
            WHERE SupplierId = ?";

        $stmt = $this->conn->prepare($sql);
        $types .= "i";
        $values[] = $id;

        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }


    // DELETE
    public function delete($id)
    {
        $sql = "DELETE FROM Suppliers WHERE SupplierId = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }



}
