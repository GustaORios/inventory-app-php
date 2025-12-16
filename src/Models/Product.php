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
    public $inStock; 
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
                    InStock AS inStock,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM Products WHERE ProductId = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        $stmt->close();

        return $product;
    }

    // CREATE
    public function create(array $data)
    {
        $sql = "INSERT INTO Products (Sku, Name, Category, Brand, SupplierId, Price, InStock, CreateAt, UpdateAt) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $sku = $data['sku'];
        $name = $data['name'];
        $category = $data['category'];
        $brand = $data['brand'];
        $supplierId = $data['supplierId'];
        $price = $data['price'];
        $inStock = $data['inStock'] ?? 0; 

        $stmt->bind_param("ssssidi", $sku, $name, $category, $brand, $supplierId, $price, $inStock);

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

        $allowed = ['sku', 'name', 'category', 'brand', 'supplierId', 'price', 'inStock'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = ucfirst($field) . " = ?";
                $values[] = $data[$field];
                $types .= ($field === 'price' ? 'd' : (($field === 'supplierId' || $field === 'inStock') ? 'i' : 's'));
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

    // DELETE
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

    public function addStock(int $id, int $quantity): bool
    {
        $sql = "UPDATE Products SET InStock = InStock + ?, UpdateAt = NOW() WHERE ProductId = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $quantity, $id); 
        if (!$stmt->execute()) {
             throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $affected = $stmt->affected_rows > 0;
        $stmt->close();
        return $affected;
    }
}
    