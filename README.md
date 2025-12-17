#  Inventory Management System (PHP API)

##  Project Overview

This project is a robust, custom **RESTful API** developed in native **PHP**, following the **MVC (Model–View–Controller)** architectural pattern.

Its primary goal is to provide a **secure, scalable, and well-structured backend** for managing inventory, suppliers, and purchase orders. The API is designed to be consumed by a **Single Page Application (SPA)**, acting exclusively as a backend service.

> **Frontend Architecture Concept**
> This project follows the **Backend for Frontend (BFF)** concept, serving as a tailored API layer specifically built to support the needs of the consuming SPA.

---

##  Features

* **Product Management**: Create, read, update, and delete inventory items.
* **Procurement**: Manage suppliers and purchase orders.
* **Dashboard**: Analytics and summary statistics for inventory health.
* **Security**: Custom access control and input sanitization.
* **Architecture**: Clean separation of concerns using a custom MVC structure.

---

##  Project Structure

The project follows a strict separation between public assets and core logic:

```text
INVENTORY-APP-PHP/
├── logs/
│   └── app.log                   # Application runtime logs
├── public/
│   └── index.php                 # Application entry point (Front Controller)
├── src/
│   ├── Common/                   # Core utilities & infrastructure
│   │   ├── AccessControl.php     # Access control logic
│   │   ├── Audit.php             # Audit handling
│   │   ├── config.php            # Application configuration
│   │   ├── Logger.php            # Logging service
│   │   ├── Response.php          # HTTP response handler
│   │   ├── Router.php            # Request router
│   │   └── Sanitizer.php         # Input sanitization
│   │
│   ├── Controllers/              # HTTP request handlers
│   │   ├── DashboardController.php
│   │   ├── ProductController.php
│   │   ├── PurchaseOrderController.php
│   │   ├── SupplierController.php
│   │   └── UserProviderController.php
│   │
│   └── Models/                   # Data access & business logic
│       ├── AuditLog.php
│       ├── Product.php
│       ├── PurchaseOrder.php
│       ├── Supplier.php
│       └── User.php
├── inventory-procurement-spa.sql # Database import file
├── .gitignore
└── README.md                     # Project documentation
```

---

```text
INVENTORY-APP-PHP/
├── logs/
│   └── app.log                   # Application runtime logs
├── public/
│   └── index.php                 # Application entry point (Front Controller)
├── src/
│   ├── Common/                   # Utilities (Config, Router, Logger, DB)
│   ├── Controllers/              # HTTP request handlers
│   └── Models/                   # Data access objects (DAO) & logic
├── inventory-procurement-spa.sql # Database import file
└── README.md                     # Project documentation

````

---

##  Setup & Installation

Follow the steps below to get the API running in your local development environment.


### 1️⃣ Database Setup

Create a database named `inventory_db` (or similar) and import the schema and data:

```bash
mysql -u root -p inventory_db < inventory-procurement-spa.sql
````

---

### 2️⃣ Configuration

Go to:

```text
src/Common/config.php
```

Edit the database connection settings:

* `DB_HOST`
* `DB_NAME`
* `DB_USER`
* `DB_PASS`

---

### 3️⃣ Server Configuration

Point your web server (**Apache** or **Nginx**) to the project folder.

> **Base URL note**
> The examples below assume the project is running in a subdirectory.

```text
http://localhost/inventory-app-php/public/index.php
```

---

##  API Reference

###  Products

| Method | Endpoint         | Description          |
| -----: | ---------------- | -------------------- |
|    GET | `/products`      | List all products    |
|    GET | `/products/{id}` | Get product details  |
|   POST | `/products`      | Create a new product |
|    PUT | `/products/{id}` | Update a product     |
|  PATCH | `/products/{id}` | Update a product     |

**Payload (POST / PUT):**

```json
{
  "name": "White A4 paper pack",
  "description": "Premium quality paper",
  "price": 12.50,
  "supplier_id": 1,
  "status": "ACTIVE"
}
```

---

###  Suppliers

| Method | Endpoint          | Description           |
| -----: | ----------------- | --------------------- |
|    GET | `/suppliers`      | List all suppliers    |
|    GET | `/suppliers/{id}` | Get supplier details  |
|   POST | `/suppliers`      | Create a new supplier |
|    PUT | `/suppliers/{id}` | Update a supplier     |
|  PATCH | `/suppliers/{id}` | Update a supplier     |

**Payload (POST / PUT):**

```json
{
  "name": "Office Supplies Co.",
  "email": "contact@officesupplies.com",
  "role": "Distributor",
  "status": "Active"
}
```

---

###  Purchase Orders

| Method | Endpoint               | Description        |
| -----: | ---------------------- | ------------------ |
|    GET | `/purchaseorders`      | List all orders    |
|    GET | `/purchaseorders/{id}` | Get order details  |
|   POST | `/purchaseorders`      | Create a new order |
|    PUT | `/purchaseorders/{id}` | Update an order    |
|  PATCH | `/purchaseorders/{id}` | Update an order    |

**Payload (POST / PUT):**

```json
{
  "supplier_id": 1,
  "order_date": "2023-10-25",
  "status": "PENDING",
  "total_amount": 1500.00
}
```

---

###  Purchase Order Items

| Method | Endpoint                   | Description              |
| -----: | -------------------------- | ------------------------ |
|    GET | `/purchaseorderitems`      | List all items in orders |
|    GET | `/purchaseorderitems/{id}` | Get item details         |
|   POST | `/purchaseorderitems`      | Add item to an order     |
|    PUT | `/purchaseorderitems/{id}` | Update item in an order  |
|  PATCH | `/purchaseorderitems/{id}` | Update item in an order  |

**Payload (POST / PUT):**

```json
{
  "purchase_order_id": 1,
  "product_id": 5,
  "quantity": 100,
  "unit_price": 12.50
}
```

---

##  Testing with Postman / Insomnia

To test the endpoints locally, combine your local server address with the endpoint path.

**Example request:**

```http
POST http://localhost/inventory-app-php/public/index.php/products
```

**Headers:**

```http
Content-Type: application/json
Accept: application/json
```

---

##  License

This project is open-source.
