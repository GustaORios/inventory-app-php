<?php

namespace Src\Models;

class PurchaseOrder
{
    private $conn;

    public function __construct()
    {
        // Captura o objeto de conexão retornado pelo config.php
        $this->conn = require __DIR__ . '/../Common/config.php';
    }

    public function getAll()
    {
        $sql = "SELECT 
                    OrderId AS id,
                    SupplierId AS supplierId,
                    TotalAmount AS totalAmount,
                    Status AS status,
                    OrderDate AS orderDate,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM purchaseorder
                ORDER BY OrderId DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new \Exception("DB query failed: " . $this->conn->error);
        }

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        return $orders;
    }

    public function getById($id)
    {
        $sql = "SELECT 
                    OrderId AS id,
                    SupplierId AS supplierId,
                    TotalAmount AS totalAmount,
                    Status AS status,
                    OrderDate AS orderDate,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM purchaseorder WHERE OrderId = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if (!$order) {
            $stmt->close();
            return null;
        }

        // Busca os itens do pedido. CORRIGIDO: Unit_Price -> PriceAtPurchase
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

        $stmt->close();
        $itemsStmt->close();

        return $order;
    }

    public function create(array $data)
    {
        $this->conn->begin_transaction();
        
        try {
            // 1. Inserir na tabela PurchaseOrder
            $orderSql = "INSERT INTO purchaseorder (SupplierId, TotalAmount, Status, OrderDate, CreateAt, UpdateAt) 
                         VALUES (?, ?, 'Pending', CURDATE(), NOW(), NOW())";
            $orderStmt = $this->conn->prepare($orderSql);
            if (!$orderStmt) throw new \Exception("DB prepare failed: " . $this->conn->error);
            
            // O TotalAmount será atualizado depois, inserimos um placeholder
            $supplierId = $data['supplierId'];
            $zeroTotal = 0.00; 
            $orderStmt->bind_param("id", $supplierId, $zeroTotal);
            if (!$orderStmt->execute()) throw new \Exception("DB execute failed: " . $orderStmt->error);
            $orderId = $this->conn->insert_id;
            $orderStmt->close();

            $total = 0.00;
            
            // 2. Inserir na tabela OrderItems
            // CORRIGIDO: Unit_Price -> PriceAtPurchase
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

            // 3. Atualizar o TotalAmount na tabela PurchaseOrder
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
    
    // Função auxiliar para obter o preço (necessário para calcular o TotalAmount)
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
