<?php

namespace Src\Controllers;

use Src\Common\Response;
use Src\Models\Supplier;

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
            Response::error("Internal Server Error: " . $e->getMessage(), 500);
        }
    }

    public function getById($id)
    {
        $supplier = $this->supplierModel->getById($id);

        if ($supplier) {
            Response::json($supplier, 200, "Supplier fetched successfully.");
        } else {
            Response::error("Supplier not found.", 404);
        }
    }

    // create...

    //delete..

    //update
}
