<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

spl_autoload_register(function ($class) {
    $prefix = 'Src\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});


use Src\Common\Router;
use Src\Common\Response;

session_start();

$router = new Router();

//suppliers route
$router->add('GET', '/suppliers', 'SupplierController@getAll');
$router->add('GET', '/suppliers/:id', 'SupplierController@getById');
$router->add('POST', '/suppliers', 'SupplierController@create');
$router->add('PUT', '/suppliers/:id', 'SupplierController@update');
$router->add('PATCH', '/suppliers/:id', 'SupplierController@update');
$router->add('DELETE', '/suppliers/:id', 'SupplierController@delete');


// purchase orders routers
$router->add('GET', '/purchaseorders', 'PurchaseOrderController@getAll');
$router->add('GET', '/purchaseorders/:id', 'PurchaseOrderController@getById');
$router->add('POST', '/purchaseorders', 'PurchaseOrderController@create');
$router->add('PUT', '/purchaseorders/:id', 'PurchaseOrderController@update');
$router->add('PATCH', '/purchaseorders/:id', 'PurchaseOrderController@update');
$router->add('DELETE', '/purchaseorders/:id', 'PurchaseOrderController@delete');



// products routers
$router->add('GET', '/products', 'ProductController@getAll');
$router->add('GET', '/products/:id', 'ProductController@getById');
$router->add('POST', '/products', 'ProductController@create');
$router->add('PUT', '/products/:id', 'ProductController@update');
$router->add('PATCH', '/products/:id', 'ProductController@update');
$router->add('DELETE', '/products/:id', 'ProductController@delete');

// user router
$router->add('POST', '/auth/login', 'UserProviderController@login');
$router->add('POST', '/auth/logout', 'UserProviderController@logout');
$router->add('POST', '/auth/register', 'UserProviderController@register');

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) { // remove query string to avoid errors on router
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$scriptName = $_SERVER['SCRIPT_NAME'];  // /inventory-app-php/public/index.php
// $uri = /inventory-app-php/public/index.php/...
if (strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName)); // /...
} 

// echo $uri ; exit;

try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    Response::error("Critical Error", 500);
}