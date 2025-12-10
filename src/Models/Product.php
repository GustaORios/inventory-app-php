<?php
namespace Src\Models;

class Product {
    public $id;
    public $name;
    public $sku;
    public $price;
    public $status; // ex: 'IN_STOCK', 'OUT_OF_STOCK'

    // Simulação de banco de dados (Mock Data) 
    public function getAll() {
        return [
            [
                'id' => 'prod-001',
                'name' => 'Laptop Gamer',
                'sku' => 'DELL-G15',
                'price' => 1200.00,
                'status' => 'IN_STOCK'
            ],
            [
                'id' => 'prod-002',
                'name' => 'Mouse Wireless',
                'sku' => 'LOGI-M123',
                'price' => 25.50,
                'status' => 'IN_STOCK'
            ]
        ];
    }

    public function getById($id) {
        // Futuro: Implementar SELECT * FROM products WHERE id = ? [cite: 10, 37]
        return [
            'id' => $id,
            'name' => 'Laptop Gamer',
            'sku' => 'DELL-G15',
            'price' => 1200.00,
            'status' => 'IN_STOCK'
        ];
    }

    public function create($data) {
        // Futuro: Implementar INSERT INTO... [cite: 10]
        return "Product created successfully (Mock ID: " . uniqid() . ")";
    }

    public function update($id, $data) {
        // Futuro: Implementar UPDATE...
        return "Product $id updated successfully.";
    }

    public function delete($id) {
        // Futuro: Implementar DELETE...
        return "Product $id deleted successfully.";
    }
}