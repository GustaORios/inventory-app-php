<?php

namespace Src\Controllers;

use function Src\Common\require_auth;
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
    private $conn;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
        $this->userModel = new User();
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

    public function getAll()
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
            $userId = require_auth($this->conn);

            AccessControl::enforceRoles(
                $this->conn,
                $userId,
                [
                    AccessControl::ROLE_MANAGER,
                    AccessControl::ROLE_ADMIN
                ]
            );

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
            $userId = require_auth($this->conn);

            AccessControl::enforceRoles(
                $this->conn,
                $userId,
                [
                    AccessControl::ROLE_MANAGER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            $input = Sanitizer::cleanArray($input);

            if (!isset($input['email']) || !Sanitizer::validateEmail($input['email'])) {
                Response::error("Invalid email format.", 400);
                return;
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

            $newUserId = $this->userModel->create($input);

            $newSupplierId = $this->supplierModel->create([
                'userId'  => $newUserId,
                'name'    => $input['name'],
                'email'   => $input['email'],
                'address' => $input['address'],
                'phone'   => $input['phone']
            ]);

            Audit::created('Supplier', (int)$newSupplierId);

            Response::json(['id' => $newSupplierId, 'userId' => $newUserId], 201, "Supplier created successfully.");
        } catch (\Exception $e) {
            Logger::error("SupplierController@create: " . $e->getMessage());
            Response::error("Internal Server Error", 500);
        }
    }

    public function delete($id)
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
            $userId = require_auth($this->conn);

            AccessControl::enforceRoles(
                $this->conn,
                $userId,
                [
                    AccessControl::ROLE_MANAGER,
                    AccessControl::ROLE_ADMIN
                ]
            );

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
