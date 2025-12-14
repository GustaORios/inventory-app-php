<?php

namespace Src\Controllers;

use Src\Common\AccessControl;
use Src\Common\Response;
use Src\Models\Product;
use Src\Common\Audit;
use Src\Common\Logger;

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function getAll()
    {
        try {
            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

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

            AccessControl::enforceRoles([
                AccessControl::ROLE_MANAGER,
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource


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

            AccessControl::enforceRoles([
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource


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
            AccessControl::enforceRoles([
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource

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

            AccessControl::enforceRoles([
                AccessControl::ROLE_PICKER,
                AccessControl::ROLE_ADMIN
            ]); // validate if role is allowed to access this resource


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
