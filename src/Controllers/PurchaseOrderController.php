<?php
namespace Src\Controllers;

use Src\Common\Response;
use Src\Models\PurchaseOrder;

class PurchaseOrderController {
    
    private $orderModel;

    public function __construct() {
        $this->orderModel = new PurchaseOrder();
    }

    public function getAll() {
        try {
            $orders = $this->orderModel->getAll();
            Response::json(['orders' => $orders], 200, "Purchase orders fetched successfully.");
        } catch (\Exception $e) {
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getById($id) {
        try {
            $order = $this->orderModel->getById($id);
            if (!$order) {
                return Response::error("Purchase Order not found", 404);
            }
            Response::json(['order' => $order], 200, "Purchase Order details fetched.");
        } catch (\Exception $e) {
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function create() {
        try {
            // Em produção: $data = json_decode(file_get_contents('php://input'), true);
            $data = []; 
            
            // Validação básica (exemplo)
            if (empty($data)) {
                // Apenas para exemplo, não vamos bloquear agora pois não temos input real
                // return Response::error("Invalid input data", 400);
            }

            $result = $this->orderModel->create($data);
            Response::json(['result' => $result], 201, "Purchase Order created.");
        } catch (\Exception $e) {
            Response::error("Error creating order: " . $e->getMessage(), 500);
        }
    }

    public function update($id) {
        try {
            $data = []; // Capturar input
            $result = $this->orderModel->update($id, $data);
            //se o status da ordem for deliveried, atualizar o estoque

            Response::json(['result' => $result], 200, "Purchase Order updated.");
        } catch (\Exception $e) {
            Response::error("Error updating order: " . $e->getMessage(), 500);
        }
    }

    public function delete($id) {
        try {
            $result = $this->orderModel->delete($id);
            \Src\Models\AuditLog::log("DELETE_PRODUCT", "Product ID $id was deleted.");
            Response::json(['result' => $result], 200, "Purchase Order deleted.");
        } catch (\Exception $e) {
            Response::error("Error deleting order: " . $e->getMessage(), 500);
        }
    }
}