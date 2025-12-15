<?php

namespace Src\Controllers;

use Src\Common\AccessControl;
use Src\Common\Response;
use Src\Models\Supplier;
use Src\Models\User;
use Src\Common\Audit;
use Src\Common\Logger;
use Src\Common\Sanitizer; 

class SupplierController
{
    private $supplierModel;
    private $userModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
        $this->userModel = new User();
    }

    public function getAll()
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

            $suppliers = $this->supplierModel->getAll();
            Response::json(['suppliers' => $suppliers], 200, "List of suppliers fetched successfully.");
        } catch (\Exception $e) {
            Logger::error("SupplierController@getAll: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getById($id)
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

            $supplier = $this->supplierModel->getById($id);

            if ($supplier) {
                Response::json($supplier, 200, "Supplier fetched successfully.");
            } else {
                Response::error("Supplier not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("SupplierController@getById: " . $e->getMessage());
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
          
            $input = Sanitizer::cleanArray($input);

            
            if (!Sanitizer::validateEmail($input['email'])) {
                Response::error("Invalid email format.", 400); return;
            }

            $requiredUser = ['username', 'email', 'password', 'role'];
            $requiredSupplier = ['name', 'address', 'phone'];

            foreach ($requiredUser as $field) {
                if (!isset($input[$field]) || trim($input[$field]) === '') {
                    Response::error("Missing user field: {$field}", 400);
                    return;
                }
            }

            foreach ($requiredSupplier as $field) {
                if (!isset($input[$field]) || trim($input[$field]) === '') {
                    Response::error("Missing supplier field: {$field}", 400);
                    return;
                }
            }

            $userId = $this->userModel->create($input);

            $newSupplierId = $this->supplierModel->create([
                'userId'  => $userId,
                'name'    => $input['name'],
                'email'   => $input['email'],
                'address' => $input['address'],
                'phone'   => $input['phone']
            ]);

            Audit::created('Supplier', (int)$newSupplierId);

            Response::json(['id' => $newSupplierId, 'userId' => $userId], 201, "Supplier created successfully.");
        } catch (\Exception $e) {
            Logger::error("SupplierController@create: " . $e->getMessage());
            Response::error("Internal Server Error", 500);
        }
    }

    public function delete($id)
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

            $deleted = $this->supplierModel->delete($id);

            if ($deleted) {
                Audit::deleted('Supplier', (int)$id);
                Response::json(null, 200, "Supplier deleted successfully.");
            } else {
                Response::error("Supplier not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("SupplierController@delete: " . $e->getMessage());
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

            $updated = $this->supplierModel->update($id, $input);

            if ($updated) {
                Audit::updated('Supplier', (int)$id);
                Response::json(null, 200, "Supplier updated successfully.");
            } else {
                Response::error("Supplier not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("SupplierController@update: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }
}
