<?php
namespace Src\Controllers;

use Src\Common\Response;
use Src\Models\PurchaseOrder;
use Src\Common\Audit;
use Src\Common\Logger;
use Src\Common\Sanitizer; 
use Src\Models\Product;    

class PurchaseOrderController
{
    private $purchaseOrderModel;

    public function __construct()
    {
        $this->purchaseOrderModel = new PurchaseOrder();
    }

    public function getAll()
    {
        try {
            $orders = $this->purchaseOrderModel->getAll();
            Response::json(['purchaseOrders' => $orders], 200, "List of purchase orders fetched successfully.");
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@getAll: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getById($id)
    {
        try {
            $order = $this->purchaseOrderModel->getById((int)$id);

            if ($order) {
                Response::json($order, 200, "Purchase order fetched successfully.");
            } else {
                Response::error("Purchase order not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@getById: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) { Response::error("Invalid JSON body.", 400); return; }

            // 1. Aplicando Sanitização
            $input = Sanitizer::cleanArray($input);

            // Validação de campos obrigatórios
            if (empty($input['supplierId']) || empty($input['items'])) {
                Response::error("Missing supplier ID or items.", 400); return;
            }

            $result = $this->purchaseOrderModel->create($input);
            Audit::created('PurchaseOrder', (int)$result);
            Response::json(['id' => $result], 201, "Purchase Order created successfully.");
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@create: " . $e->getMessage());
            Response::error("Error creating order: " . $e->getMessage(), 500);
        }
    }


    public function update($id)
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) { Response::error("Invalid JSON body.", 400); return; }

            // 1. Sanitização
            $input = Sanitizer::cleanArray($input);

            // 2. Buscar o status atual ANTES de atualizar
            $currentOrder = $this->purchaseOrderModel->getById((int)$id);
            if (!$currentOrder) { Response::error("Order not found.", 404); return; }

            $oldStatus = strtoupper($currentOrder['status'] ?? '');
            $newStatus = isset($input['status']) ? strtoupper($input['status']) : $oldStatus;

            
            $updated = $this->purchaseOrderModel->update((int)$id, $input);

            if ($updated) {
                
                if ($newStatus === 'RECEIVED' && $oldStatus !== 'RECEIVED') {
                    
                    $updatedOrder = $this->purchaseOrderModel->getById((int)$id); 
                    
                    if (!empty($updatedOrder['items'])) {
                        
                        $productModel = new Product(); 
                        
                        foreach ($updatedOrder['items'] as $item) {
                            
                            $productModel->addStock($item['productId'], $item['quantity']); 
                        }
                        Logger::info("Stock updated for Order ID: $id (Received)");
                    } else {
                        Logger::info("Order ID: $id received, but no items found.");
                    }
                }

                Audit::updated('PurchaseOrder', (int)$id);
                Response::json(null, 200, "Purchase order updated successfully.");
            } else {
                Response::error("Order not found or no changes made.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@update: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $deleted = $this->purchaseOrderModel->delete((int)$id);

            if ($deleted) {
                Audit::deleted('PurchaseOrder', (int)$id);
                Response::json(null, 200, "Purchase order deleted successfully.");
            } else {
                Response::error("Purchase order not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@delete: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }
}

