<?php
namespace Src\Models;

class Product
{
    public $id;
    public $sku;
    public $name;
    public $category;
    public $brand;
    public $supplierId;
    public $price;
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

    // GET ALL
    public function getAll()
    {
        $sql = "SELECT 
                    ProductId AS id,
                    Sku AS sku,
                    Name AS name,
                    Category AS category,
                    Brand AS brand,
                    SupplierId AS supplierId,
                    Price AS price,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM Products";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new \Exception("DB query failed: " . $this->conn->error);
        }

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }

    // GET BY ID
    public function getById($id)
    {
        $sql = "SELECT 
                    ProductId AS id,
                    Sku AS sku,
                    Name AS name,
                    Category AS category,
                    Brand AS brand,
                    SupplierId AS supplierId,
                    Price AS price,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM Products
                WHERE ProductId = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        $stmt->close();

        return $product ?: null;
    }

    // CREATE
    public function create(array $data)
    {
        $sql = "INSERT INTO Products (Sku, Name, Category, Brand, SupplierId, Price, CreateAt, UpdateAt)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $sku = $data['sku'];
        $name = $data['name'];
        $category = $data['category'];
        $brand = $data['brand'];
        $supplierId = (int)$data['supplierId'];
        $price = (float)$data['price'];

        // sku(s), name(s), category(s), brand(s), supplierId(i), price(d)
        $stmt->bind_param("ssssid", $sku, $name, $category, $brand, $supplierId, $price);

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $newId = $this->conn->insert_id;
        $stmt->close();

        return $newId;
    }

    // UPDATE (PUT/PATCH)
    public function update($id, array $data)
    {
        $fields = [];
        $values = [];
        $types  = "";

        $allowed = ['sku', 'name', 'category', 'brand', 'supplierId', 'price'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $column = match ($field) {
                    'supplierId' => 'SupplierId',
                    default => ucfirst($field),
                };

                $fields[] = $column . " = ?";

                if ($field === 'supplierId') {
                    $values[] = (int)$data[$field];
                    $types .= "i";
                } elseif ($field === 'price') {
                    $values[] = (float)$data[$field];
                    $types .= "d";
                } else {
                    $values[] = $data[$field];
                    $types .= "s";
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE Products SET " . implode(", ", $fields) . ", UpdateAt = NOW()
                WHERE ProductId = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $types .= "i";
        $values[] = (int)$id;

        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        $affected = $stmt->affected_rows > 0;
        $stmt->close();

        return $affected;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM Products WHERE ProductId = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $affected = $stmt->affected_rows > 0;
        $stmt->close();

        return $affected;
    }
}
