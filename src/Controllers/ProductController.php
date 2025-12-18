<?php

namespace Src\Controllers;

use function Src\Common\require_auth;
use Src\Common\AccessControl;
use Src\Common\Response;
use Src\Models\Product;
use Src\Common\Audit;
use Src\Common\Logger;

class ProductController
{
    private $productModel;
    private $conn;

    public function __construct()
    {
        $this->productModel = new Product();
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
                    AccessControl::ROLE_PICKER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $products = $this->productModel->getAll();
            Response::json(['products' => $products], 200, "List of products fetched successfully.");
        } catch (\Exception $e) {
            Logger::error("ProductController@getAll: " . $e->getMessage());
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
                    AccessControl::ROLE_PICKER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $product = $this->productModel->getById($id);

            if ($product) {
                Response::json($product, 200, "Product fetched successfully.");
            } else {
                Response::error("Product not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("ProductController@getById: " . $e->getMessage());
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
                    AccessControl::ROLE_PICKER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            $required = ['name', 'sku', 'price'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
                    Response::error("Missing field: {$field}", 400);
                    return;
                }
            }

            $newProductId = $this->productModel->create($input);

            Audit::created('Product', (int)$newProductId);

            Response::json(['id' => $newProductId], 201, "Product created successfully.");
        } catch (\Exception $e) {
            Logger::error("ProductController@create: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
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
                    AccessControl::ROLE_PICKER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $deleted = $this->productModel->delete($id);

            if ($deleted) {
                Audit::deleted('Product', (int)$id);
                Response::json(null, 200, "Product deleted successfully.");
            } else {
                Response::error("Product not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("ProductController@delete: " . $e->getMessage());
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
                    AccessControl::ROLE_PICKER,
                    AccessControl::ROLE_ADMIN
                ]
            );

            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                Response::error("Invalid JSON body.", 400);
                return;
            }

            $updated = $this->productModel->update($id, $input);

            if ($updated) {
                Audit::updated('Product', (int)$id);
                Response::json(null, 200, "Product updated successfully.");
            } else {
                Response::error("Product not found.", 404);
            }
        } catch (\Exception $e) {
            Logger::error("ProductController@update: " . $e->getMessage());
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }
}
