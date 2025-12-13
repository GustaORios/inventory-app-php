# inventory-app-php

# GET
/inventory-app-php/public/index.php/products
/inventory-app-php/public/index.php/products/id

/inventory-app-php/public/index.php/purchaseorders
/inventory-app-php/public/index.php/purchaseorders/id

/inventory-app-php/public/index.php/purchaseorderitems
/inventory-app-php/public/index.php/purchaseorderitems/id

/inventory-app-php/public/index.php/suppliers
/inventory-app-php/public/index.php/suppliers/id



# POST
/inventory-app-php/public/index.php/suppliers
{
  "name": "insertName",
  "email": "insertEMail",
  "role": "insertRole",
  "status": "insertStatus"
}

/inventory-app-php/public/index.php/products
{
  "name": "insert",
  "description": "White A4 paper pack",
  "price": 12.50,
  "supplier_id": 1,
  "status": "ACTIVE"
}

/inventory-app-php/public/index.php/purchaseorders
{
  "supplier_id": insertSupplierId,
  "order_date": "insertOrderDate",
  "status": "insertStatus",
  "total_amount": insertTotalAmount
}

/inventory-app-php/public/index.php/purchaseorderitems
{
  "purchase_order_id": insertPurchaseOrderId,
  "product_id": insertProductId,
  "quantity": insertQuantity,
  "unit_price": insertUnitPrice
}

# PATCH (INPUTS ARE OPTIONAL) AND PUT

/inventory-app-php/public/index.php/suppliers/id
{
  "name": "insertName",
  "email": "insertEMail",
  "role": "insertRole",
  "status": "insertStatus"
}

/inventory-app-php/public/index.php/products/id
{
  "name": "insert",
  "description": "White A4 paper pack",
  "price": 12.50,
  "supplier_id": 1,
  "status": "ACTIVE"
}

/inventory-app-php/public/index.php/purchaseorders/id
{
  "supplier_id": insertSupplierId,
  "order_date": "insertOrderDate",
  "status": "insertStatus",
  "total_amount": insertTotalAmount
}

/inventory-app-php/public/index.php/purchaseorderitems/id
{
  "purchase_order_id": insertPurchaseOrderId,
  "product_id": insertProductId,
  "quantity": insertQuantity,
  "unit_price": insertUnitPrice
}

