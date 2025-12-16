<?php

namespace Src\Models;

class PurchaseOrder
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


    private function getSupplierIdIfSupplier(): ?int
    {
        if (isset($_SESSION['userinfo']['role']) && $_SESSION['userinfo']['role'] === 'supplier') {
            return (int)($_SESSION['userinfo']['id'] ?? null);
        }
        return null;
    }

    public function getAll()
    {
        $supplierId = $this->getSupplierIdIfSupplier(); // validate if supplier role to filter order when select

        $sql = "SELECT
                    OrderId AS id,
                    SupplierId AS supplierId,
                    TotalAmount AS totalAmount,
                    Status AS status,
                    OrderDate AS orderDate,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM purchaseorder";

        $params = "";
        $bind_values = [];

        if ($supplierId !== null) {
            $sql .= " WHERE SupplierId = ?";
            $params = "i";
            $bind_values[] = $supplierId;
        }

        $sql .= " ORDER BY OrderId DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        if ($params) {
             $stmt->bind_param($params, ...$bind_values);
        }

        if (!$stmt->execute()) {
            throw new \Exception("DB execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();

        return $orders;
    }

    public function getById($id)
    {
        $supplierId = $this->getSupplierIdIfSupplier();

        $sql = "SELECT
                    OrderId AS id,
                    SupplierId AS supplierId,
                    TotalAmount AS totalAmount,
                    Status AS status,
                    OrderDate AS orderDate,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM purchaseorder
                WHERE OrderId = ?";

        $params = "i";
        $bind_values = [$id];

        if ($supplierId !== null) {
            $sql .= " AND SupplierId = ?";
            $params .= "i";
            $bind_values[] = $supplierId;
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param($params, ...$bind_values);

        if (!$stmt->execute()) throw new \Exception("DB execute failed: " . $stmt->error);

        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if (!$order) return null;

        $sql2 = "SELECT
                    poi.OrderId AS orderId,
                    poi.ProductId AS productId,
                    poi.Quantity AS quantity,
                    poi.PriceAtPurchase AS priceAtPurchase,
                    (poi.Quantity * poi.PriceAtPurchase) AS lineTotal
                FROM purchaseorderitems poi
                WHERE poi.OrderId = ?";

        $stmt2 = $this->conn->prepare($sql2);
        if (!$stmt2) throw new \Exception("DB prepare failed: " . $this->conn->error);

        if (!$order) {
            $stmt->close();
            return null;
        }
        $itemsSql = "SELECT ProductId AS productId, Quantity AS quantity, PriceAtPurchase AS unitPrice 
                     FROM purchaseorderitems WHERE OrderId = ?";
        
        $itemsStmt = $this->conn->prepare($itemsSql);
        if (!$itemsStmt) {
            throw new \Exception("DB prepare failed for items: " . $this->conn->error);
        }

        $itemsStmt->bind_param("i", $id);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }

        $order['items'] = $items;

        $itemsStmt->close();

        return $order;
    }

    public function create(array $data)
    {
        $this->conn->begin_transaction();
        
        try {
            $orderSql = "INSERT INTO purchaseorder (SupplierId, TotalAmount, Status, OrderDate, CreateAt, UpdateAt) 
                         VALUES (?, ?, 'Pending', CURDATE(), NOW(), NOW())";
            $orderStmt = $this->conn->prepare($orderSql);
            if (!$orderStmt) throw new \Exception("DB prepare failed: " . $this->conn->error);
            $supplierId = $data['supplierId'];
            $zeroTotal = 0.00; 
            $orderStmt->bind_param("id", $supplierId, $zeroTotal);
            if (!$orderStmt->execute()) throw new \Exception("DB execute failed: " . $orderStmt->error);
            $orderId = $this->conn->insert_id;
            $orderStmt->close();

            $total = 0.00;
            $itemSql = "INSERT INTO purchaseorderitems (OrderId, ProductId, Quantity, PriceAtPurchase) VALUES (?, ?, ?, ?)";
            $itemStmt = $this->conn->prepare($itemSql);
            if (!$itemStmt) throw new \Exception("DB prepare failed for items: " . $this->conn->error);

            foreach ($data['items'] as $item) {
                $price = $this->getProductPrice((int)$item['productId']); // Função auxiliar para buscar o preço
                $lineTotal = $price * $item['quantity'];
                $total += $lineTotal;

                $itemStmt->bind_param("iiid", $orderId, $item['productId'], $item['quantity'], $price);
                if (!$itemStmt->execute()) throw new \Exception("DB execute failed for item: " . $itemStmt->error);
            }
            $itemStmt->close();
            $up = $this->conn->prepare("UPDATE purchaseorder SET TotalAmount = ? WHERE OrderId = ?");
            if (!$up) throw new \Exception("DB prepare failed: " . $this->conn->error);

            $up->bind_param("di", $total, $orderId);
            if (!$up->execute()) throw new \Exception("DB execute failed: " . $up->error);
            $up->close();

            $this->conn->commit();
            return $orderId;

        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update($orderId, array $data)
    {
        $fields = [];
        $values = [];
        $types = "";

        $allowed = ['supplierId', 'status', 'totalAmount'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = ucfirst($field) . " = ?";
                $values[] = $data[$field];
                $types .= ($field === 'totalAmount' ? 'd' : (($field === 'supplierId') ? 'i' : 's'));
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE purchaseorder SET " . implode(", ", $fields) . ", UpdateAt = NOW()
            WHERE OrderId = ?";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $types .= "i";
        $values[] = (int)$orderId;

        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function delete($orderId)
    {
        $stmt = $this->conn->prepare("DELETE FROM purchaseorder WHERE OrderId = ?");
        if (!$stmt) throw new \Exception("DB prepare failed: " . $this->conn->error);

        $stmt->bind_param("i", $orderId);
        if (!$stmt->execute()) throw new \Exception("DB execute failed: " . $stmt->error);

        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        return $deleted;
    }
    
    private function getProductPrice(int $productId): float
    {
        $stmt = $this->conn->prepare("SELECT Price FROM products WHERE ProductId = ?");
        if (!$stmt) throw new \Exception("DB prepare failed: " . $this->conn->error);

        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) throw new \Exception("DB execute failed: " . $stmt->error);

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) throw new \Exception("Product ID not found: " . $productId);

        return (float)$row['Price'];
    }
}
