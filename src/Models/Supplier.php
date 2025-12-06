<?php
namespace Src\Models;

class Supplier {
    
    public $id;
    public $name;
    public $email;
    public $role;
    public $status;
    public $createdAt;
    public $updatedAt;

    private $mockData = [];

    public function __construct() {
        $this->mockData = [
            [
              "id" => "c4f8d8a1-8e9a-4f52-bf77-4c3d2e1c5a21",
              "name" => "Thales Trade Supplies Ltd.",
              "email" => "thales@gmail.com",
              "role" => "supplier",
              "status" => "ACTIVE",
              "createdAt" => "2024-07-15T10:23:45Z",
              "updatedAt" => "2025-11-20T01:59:03.140Z"
            ],
            [
              "id" => "b19cfd43-4f25-4bb8-8e1a-29b0dc8cb6c2",
              "name" => "Nelson Logistics Co.",
              "email" => "nelson@gmail.com",
              "role" => "supplier",
              "status" => "ACTIVE",
              "createdAt" => "2023-11-22T08:15:30Z",
              "updatedAt" => "2025-11-20T01:59:44.570Z"
            ],
            [
              "id" => "7a62c1e4-s1d8f-496c-9314-952b2c631b9ac",
              "name" => "Gustavo Rivers",
              "email" => "gustavo@saturn.com",
              "role" => "picker",
              "status" => "ACTIVE",
              "createdAt" => "2024-02-10T09:00:00Z",
              "updatedAt" => "2025-11-20T04:24:18.405Z"
            ]
        ];
    }
    
    public function getAll() {
        // todo mysql "SELECT * FROM users WHERE role IN (...)"
        return $this->mockData;
    }
    
    public function getById($id) {
        // to do mysql "SELECT * FROM users WHERE id = :id"
        foreach ($this->mockData as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

}