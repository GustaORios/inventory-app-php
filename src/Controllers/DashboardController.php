<?php
namespace Src\Controllers;

use Src\Common\Response;
use Src\Common\Logger;

class DashboardController
{
    private $conn;

    /**
     * Initializes the controller and establishes the database connection.
     */
    public function __construct()
    {
        // Load the database configuration file
        require __DIR__ . '/../Common/config.php';
        /** @var \mysqli $conn */
        // Assign the global connection variable to the class property
        $this->conn = $conn;
    }

    /**
     * Aggregates various metrics for the dashboard view.
     */
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

            // 1. Fetch general inventory metrics: total items and total monetary value
            $sqlProd = "SELECT 
                            COUNT(ProductId) as totalProducts, 
                            SUM(Price * InStock) as inventoryValue 
                        FROM products";
            $data['inventory'] = $this->conn->query($sqlProd)->fetch_assoc();

            // 2. Count how many unique products are running low on stock (less than 10 units)
            $sqlLowCount = "SELECT COUNT(ProductId) AS lowStockCount FROM products WHERE InStock < 10";
            $data['lowStockCount'] = $this->conn->query($sqlLowCount)->fetch_assoc()['lowStockCount'];

            // 3. Group purchase orders by their current status (e.g., Pending, Completed)
            $sqlOrders = "SELECT Status, COUNT(*) as count FROM purchaseorder GROUP BY Status";
            $resOrders = $this->conn->query($sqlOrders);
            $orders = [];
            while($row = $resOrders->fetch_assoc()) {
                $orders[] = $row;
            }
            $data['orders'] = $orders;

            // 4. Retrieve a detailed list of the first 5 products that need restocking
            $sqlLow = "SELECT ProductId, Name, InStock FROM products WHERE InStock < 10 LIMIT 5";
            $resLow = $this->conn->query($sqlLow);
            $lowStock = [];
            while($row = $resLow->fetch_assoc()) {
                $lowStock[] = $row;
            }
            $data['lowStockList'] = $lowStock;

            // 5. Calculate total expenditure per supplier to identify top partners
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
            while($row = $resJoin->fetch_assoc()) {
                $supplierSpend[] = $row;
            }
            $data['topSupplierSpend'] = $supplierSpend;
            
            // Return all collected metrics as a formatted JSON response
            Response::json($data, 200, "Dashboard data fetched.");

        } catch (\Exception $e) {
            // Log the specific error for debugging and return a generic error to the user
            Logger::error("Dashboard Error: " . $e->getMessage()); 
            Response::error("Internal Server Error", 500);
        }
    }
}