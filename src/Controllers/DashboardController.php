<?php
namespace Src\Controllers;

use Src\Common\Response;
use Src\Common\Logger;

class DashboardController
{
    private $conn;

    public function __construct()
    {
        // Reutiliza sua conexão do config.php
        require __DIR__ . '/../Common/config.php';
        /** @var \mysqli $conn */
        $this->conn = $conn;
    }

    public function getSummary()
    {
        try {
            $data = [];

            // 1. Total Produtos e VALOR TOTAL DO ESTOQUE
            // 'inventoryValue' é o VALOR TOTAL DE TODOS OS PRODUTOS JUNTOS (SUM(Price * InStock)).
            // OBS: Se este valor retornar 0.00, verifique se a coluna 'Price' na tabela 'products'
            // tem valores válidos e se 'InStock' é maior que zero para alguns produtos.
            $sqlProd = "SELECT 
                            COUNT(ProductId) as totalProducts, 
                            SUM(Price * InStock) as inventoryValue 
                        FROM products";
            $data['inventory'] = $this->conn->query($sqlProd)->fetch_assoc();

            // 2. TOTAL DE PRODUTOS COM ESTOQUE ABAIXO DE 10 (Requisito atendido)
            $sqlLowCount = "SELECT COUNT(ProductId) AS lowStockCount FROM products WHERE InStock < 10";
            $data['lowStockCount'] = $this->conn->query($sqlLowCount)->fetch_assoc()['lowStockCount'];
            
            // 3. Pedidos por Status
            $sqlOrders = "SELECT Status, COUNT(*) as count FROM purchaseorder GROUP BY Status";
            $resOrders = $this->conn->query($sqlOrders);
            $orders = [];
            while($row = $resOrders->fetch_assoc()) {
                $orders[] = $row;
            }
            $data['orders'] = $orders;

            // 4. PRODUTOS BAIXOS EM ESTOQUE (Lista - Mantido)
            $sqlLow = "SELECT ProductId, Name, InStock FROM products WHERE InStock < 10 LIMIT 5";
            $resLow = $this->conn->query($sqlLow);
            $lowStock = [];
            while($row = $resLow->fetch_assoc()) {
                $lowStock[] = $row;
            }
            $data['lowStockList'] = $lowStock;

            // 5. TOTAL GASTO POR FORNECEDOR (SELECT COM JOIN - Requisito atendido)
            // Usa INNER JOIN entre purchaseorder e suppliers para mostrar o gasto consolidado.
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
            
            Response::json($data, 200, "Dashboard data fetched.");
        } catch (\Exception $e) {
            Logger::error("Dashboard Error: " . $e->getMessage()); 
            Response::error("Internal Server Error", 500);
        }
    }
}