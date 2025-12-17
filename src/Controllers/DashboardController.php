<?php

namespace Src\Controllers;

use function Src\Common\require_auth;
use Src\Common\AccessControl;
use Src\Common\Response;
use Src\Common\Logger;

class DashboardController
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../Common/config.php';

        if (!($this->conn instanceof \mysqli)) {
            throw new \Exception("Database connection not available. Check config.php");
        }
    }

    public function __destruct()
    {
        if ($this->conn instanceof \mysqli) {
            $this->conn->close();
        }
    }

    public function getSummary()
    {
        try {
            $userId = require_auth($this->conn);

            AccessControl::enforceRoles(
                $this->conn,
                $userId,
                [
                    AccessControl::ROLE_MANAGER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $data = [];

            $sqlProd = "SELECT 
                            COUNT(ProductId) as totalProducts, 
                            SUM(Price * InStock) as inventoryValue 
                        FROM products";
            $data['inventory'] = $this->conn->query($sqlProd)->fetch_assoc();

            $sqlLowCount = "SELECT COUNT(ProductId) AS lowStockCount FROM products WHERE InStock < 10";
            $data['lowStockCount'] = $this->conn->query($sqlLowCount)->fetch_assoc()['lowStockCount'];

            $sqlOrders = "SELECT Status, COUNT(*) as count FROM purchaseorder GROUP BY Status";
            $resOrders = $this->conn->query($sqlOrders);
            $orders = [];
            while ($row = $resOrders->fetch_assoc()) {
                $orders[] = $row;
            }
            $data['orders'] = $orders;

            $sqlLow = "SELECT ProductId, Name, InStock FROM products WHERE InStock < 10 LIMIT 5";
            $resLow = $this->conn->query($sqlLow);
            $lowStock = [];
            while ($row = $resLow->fetch_assoc()) {
                $lowStock[] = $row;
            }
            $data['lowStockList'] = $lowStock;

            $sqlJoin = "SELECT 
                            s.Name AS supplierName, 
                            SUM(po.TotalAmount) AS totalSpent 
                        FROM purchaseorder po
                        INNER JOIN suppliers s ON po.SupplierId = s.SupplierId
                        GROUP BY s.Name
                        ORDER BY totalSpent DESC
                        LIMIT 5";
            $resJoin = $this->conn->query($sqlJoin);
            $supplierSpend = [];
            while ($row = $resJoin->fetch_assoc()) {
                $supplierSpend[] = $row;
            }
            $data['topSupplierSpend'] = $supplierSpend;

            Response::json($data, 200, "Dashboard data fetched.");
        } catch (\Exception $e) {
            Logger::error("Dashboard Error: " . $e->getMessage());
            Response::error("Internal Server Error", 500);
        }
    }
}
