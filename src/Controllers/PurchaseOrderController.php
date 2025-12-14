<?php

namespace Src\Controllers;

use Src\Common\AccessControl;
use Src\Common\Response;
use Src\Models\PurchaseOrder;
use Src\Common\Audit;
use Src\Common\Logger;

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
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_SUPPLIER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

            $userId = $_SESSION['userinfo']['id'];
            $orders = $this->purchaseOrderModel->getAll($userId); // select orders using supplier id logged in
            Response::json(['purchaseOrders' => $orders], 200, "List of purchase orders fetched successfully.");
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@getAll: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getById($id)
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_SUPPLIER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource
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
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            if (!isset($input['supplierId']) || (int)$input['supplierId'] <= 0) {
                Response::error("Missing/invalid field: supplierId", 400);
                return;
            }

            if (!isset($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
                Response::error("Missing field: items (must be a non-empty array)", 400);
                return;
            }

            foreach ($input['items'] as $idx => $item) {
                if (!isset($item['productId']) || (int)$item['productId'] <= 0) {
                    Response::error("Missing/invalid field: items[$idx].productId", 400);
                    return;
                }
                if (!isset($item['quantity']) || (int)$item['quantity'] <= 0) {
                    Response::error("Missing/invalid field: items[$idx].quantity (must be > 0)", 400);
                    return;
                }

                if (isset($item['priceAtPurchase']) && (float)$item['priceAtPurchase'] < 0) {
                    Response::error("Invalid field: items[$idx].priceAtPurchase (must be >= 0)", 400);
                    return;
                }
            }

            $newId = $this->purchaseOrderModel->create($input);

            Audit::created('PurchaseOrder', (int)$newId);

            Response::json(['orderId' => $newId], 201, "Purchase order created successfully.");
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@create: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function update($id)
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            $updated = $this->purchaseOrderModel->update((int)$id, $input);

            if ($updated) {
                Audit::updated('PurchaseOrder', (int)$id);
                Response::json(null, 200, "Purchase order updated successfully.");
            } else {
                Response::error("Purchase order not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("PurchaseOrderController@update: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

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
