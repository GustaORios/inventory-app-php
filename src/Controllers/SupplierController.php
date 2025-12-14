<?php

namespace Src\Controllers;

use Src\Common\Response;
use Src\Models\Supplier;
use Src\Common\Audit;
use Src\Common\Logger;
use Src\Common\Sanitizer; 

class SupplierController
{
    private $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
    }

    public function getAll()
    {
        try {
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
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) { Response::error("Invalid JSON body.", 400); return; }

            // 1. Sanitização
            $input = Sanitizer::cleanArray($input);

            // 2. Validação de Email
            if (!Sanitizer::validateEmail($input['email'])) {
                Response::error("Invalid email format.", 400); return;
            }

            // Validação de campos obrigatórios...
            $required = ['name', 'email', 'role', 'status'];
            foreach ($required as $field) {
                if (empty($input[$field])) { Response::error("Missing: {$field}", 400); return; }
            }

            $newSupplierId = $this->supplierModel->create($input);
            Audit::created('Supplier', (int)$newSupplierId);
            Response::json(['id' => $newSupplierId], 201, "Supplier created successfully.");
        } catch (\Exception $e) {
            Logger::error("SupplierController@create: " . $e->getMessage());
            Response::error("Internal Server Error", 500);
        }
    }

    public function delete($id)
    {
        try {
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
}
