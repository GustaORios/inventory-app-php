<?php
namespace Src\Controllers;

use Src\Common\Response;
use Src\Models\Product;

class ProductController {
    
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function getAll() {
        try {
            $products = $this->productModel->getAll();
            Response::json(['products' => $products], 200, "List of products fetched successfully.");
        } catch (\Exception $e) {
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getById($id) {
        try {
            $product = $this->productModel->getById($id);
            if (!$product) {
                return Response::error("Product not found", 404);
            }
            Response::json(['product' => $product], 200, "Product details fetched.");
        } catch (\Exception $e) {
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function create() {
        try {
            // Nota: Em um cenário real, capture o input do corpo da requisição aqui
            $data = []; 
            $result = $this->productModel->create($data);
            Response::json(['result' => $result], 201, "Product created.");
        } catch (\Exception $e) {
            Response::error("Error creating product: " . $e->getMessage(), 500);
        }
    }

    public function update($id) {
        try {
            $data = []; // Capturar input
            $result = $this->productModel->update($id, $data);
            Response::json(['result' => $result], 200, "Product updated.");
        } catch (\Exception $e) {
            Response::error("Error updating product: " . $e->getMessage(), 500);
        }
    }

    public function delete($id) {
        try {
            $result = $this->productModel->delete($id);
            \Src\Models\AuditLog::log("DELETE_PRODUCT", "Product ID $id was deleted.");
            Response::json(['result' => $result], 200, "Product deleted.");
        } catch (\Exception $e) {
            Response::error("Error deleting product: " . $e->getMessage(), 500);
        }
    }
}
?>