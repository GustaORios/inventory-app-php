<?php
namespace Src\Models;

class PurchaseOrder {
    public $id;
    public $supplier_id;
    public $total_amount;
    public $status;
    public $created_at;

    // Dados Mockados para teste
    public function getAll() {
        return [
            [
                'id' => 'po-999',
                'supplier_id' => 'c4f8d8a1-8e9a', 
                'total_amount' => 5000.00,
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 'po-1000',
                'supplier_id' => 'b19cfd43-4f25', 
                'total_amount' => 750.50,
                'status' => 'APPROVED',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    public function getById($id) {
        // Simulação de busca por ID
        return [
            'id' => $id,
            'supplier_id' => 'c4f8d8a1-8e9a',
            'total_amount' => 5000.00,
            'status' => 'PENDING',
            'items' => [
                ['product_id' => 'prod-001', 'qty' => 2, 'unit_price' => 1200.00],
                ['product_id' => 'prod-002', 'qty' => 10, 'unit_price' => 260.00]
            ],
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public function create($data) {
        // Futuro: INSERT INTO purchase_orders ...
        return "Purchase Order created successfully (Mock ID: " . uniqid() . ")";
    }

    public function update($id, $data) {
        // Futuro: UPDATE purchase_orders ...
        return "Purchase Order $id updated successfully.";
    }

    public function delete($id) {
        // Futuro: DELETE FROM purchase_orders ...
        return "Purchase Order $id deleted successfully.";
    }
}