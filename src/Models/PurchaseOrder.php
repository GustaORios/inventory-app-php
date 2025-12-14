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

    public function getAll($supplierId)
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
                WHERE SupplierId = ? 
                ORDER BY OrderId DESC";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new \Exception("DB prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $supplierId);

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
        $sql = "SELECT 
                    OrderId AS id,
                    SupplierId AS supplierId,
                    TotalAmount AS totalAmount,
                    Status AS status,
                    OrderDate AS orderDate,
                    CreateAt AS createdAt,
                    UpdateAt AS updatedAt
                FROM purchaseorder
                WHERE OrderId = ?
                  AND SupplierId = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new \Exception("DB prepare failed: " . $this->conn->error);

        $stmt->bind_param("ii", $id, $_SESSION['userinfo']['id']);
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

        $stmt2->bind_param("i", $id);
        if (!$stmt2->execute()) throw new \Exception("DB execute failed: " . $stmt2->error);

        $items = [];
        $res2 = $stmt2->get_result();
        while ($row = $res2->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt2->close();

        $order['items'] = $items;

        return $order;
    }

    public function create(array $data)
    {
        $supplierId = (int)$data['supplierId'];
        $items = $data['items'];

        $this->conn->begin_transaction();

        try {
            $sql = "INSERT INTO purchaseorder (SupplierId, TotalAmount)
                    VALUES (?, 0)";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) throw new \Exception("DB prepare failed: " . $this->conn->error);

            $stmt->bind_param("i", $supplierId);
            if (!$stmt->execute()) throw new \Exception("DB execute failed: " . $stmt->error);

            $orderId = $this->conn->insert_id;
            $stmt->close();

            $total = 0.0;

            foreach ($items as $item) {
                $productId = (int)$item['productId'];
                $quantity  = (int)$item['quantity'];

                $price = isset($item['priceAtPurchase'])
                    ? (float)$item['priceAtPurchase']
                    : $this->getProductPrice($productId);

                $sqlItem = "INSERT INTO purchaseorderitems (OrderId, ProductId, Quantity, PriceAtPurchase)
                            VALUES (?, ?, ?, ?)";

                $stmtItem = $this->conn->prepare($sqlItem);
                if (!$stmtItem) throw new \Exception("DB prepare failed: " . $this->conn->error);

                $stmtItem->bind_param("iiid", $orderId, $productId, $quantity, $price);
                if (!$stmtItem->execute()) throw new \Exception("DB execute failed: " . $stmtItem->error);

                $stmtItem->close();

                $total += ($quantity * $price);
            }

            $sqlTotal = "UPDATE purchaseorder SET TotalAmount = ? WHERE OrderId = ?";
            $stmtTotal = $this->conn->prepare($sqlTotal);
            if (!$stmtTotal) throw new \Exception("DB prepare failed: " . $this->conn->error);

            $stmtTotal->bind_param("di", $total, $orderId);
            if (!$stmtTotal->execute()) throw new \Exception("DB execute failed: " . $stmtTotal->error);

            $stmtTotal->close();

            $this->conn->commit();
            return $orderId;

        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update($orderId, array $data)
    {
        $exists = $this->getById((int)$orderId);
        if (!$exists) return false;

        $this->conn->begin_transaction();

        try {
            if (isset($data['supplierId'])) {
                $supplierId = (int)$data['supplierId'];

                $sql = "UPDATE purchaseorder SET SupplierId = ? WHERE OrderId = ?";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) throw new \Exception("DB prepare failed: " . $this->conn->error);

                $stmt->bind_param("ii", $supplierId, $orderId);
                if (!$stmt->execute()) throw new \Exception("DB execute failed: " . $stmt->error);
                $stmt->close();
            }

            if (isset($data['status'])) {
                $status = strtoupper(trim((string)$data['status']));

                $allowedStatuses = ['PENDING', 'APPROVED', 'CANCELLED', 'RECEIVED'];
                if (!in_array($status, $allowedStatuses, true)) {
                    throw new \Exception("Invalid status. Allowed: " . implode(", ", $allowedStatuses));
                }

                $sql = "UPDATE purchaseorder SET Status = ? WHERE OrderId = ?";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) throw new \Exception("DB prepare failed: " . $this->conn->error);

                $stmt->bind_param("si", $status, $orderId);
                if (!$stmt->execute()) throw new \Exception("DB execute failed: " . $stmt->error);
                $stmt->close();
            }

            if (isset($data['items']) && is_array($data['items'])) {
                $del = $this->conn->prepare("DELETE FROM purchaseorderitems WHERE OrderId = ?");
                if (!$del) throw new \Exception("DB prepare failed: " . $this->conn->error);

                $del->bind_param("i", $orderId);
                if (!$del->execute()) throw new \Exception("DB execute failed: " . $del->error);
                $del->close();

                $total = 0.0;

                foreach ($data['items'] as $item) {
                    $productId = (int)$item['productId'];
                    $quantity  = (int)$item['quantity'];

                    $price = isset($item['priceAtPurchase'])
                        ? (float)$item['priceAtPurchase']
                        : $this->getProductPrice($productId);

                    $ins = $this->conn->prepare(
                        "INSERT INTO purchaseorderitems (OrderId, ProductId, Quantity, PriceAtPurchase)
                         VALUES (?, ?, ?, ?)"
                    );
                    if (!$ins) throw new \Exception("DB prepare failed: " . $this->conn->error);

                    $ins->bind_param("iiid", $orderId, $productId, $quantity, $price);
                    if (!$ins->execute()) throw new \Exception("DB execute failed: " . $ins->error);
                    $ins->close();

                    $total += ($quantity * $price);
                }

                $up = $this->conn->prepare("UPDATE purchaseorder SET TotalAmount = ? WHERE OrderId = ?");
                if (!$up) throw new \Exception("DB prepare failed: " . $this->conn->error);

                $up->bind_param("di", $total, $orderId);
                if (!$up->execute()) throw new \Exception("DB execute failed: " . $up->error);
                $up->close();
            }

            $this->conn->commit();
            return true;

        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
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

        if (!$row) {
            throw new \Exception("Product not found: " . $productId);
        }

        return (float)$row['Price'];
    }
}
