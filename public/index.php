<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
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

// Suppliers routes
$router->add('GET', '/suppliers', 'SupplierController@getAll');
$router->add('GET', '/suppliers/:id', 'SupplierController@getById');
$router->add('POST', '/suppliers', 'SupplierController@create');
$router->add('PUT', '/suppliers/:id', 'SupplierController@update');
$router->add('PATCH', '/suppliers/:id', 'SupplierController@update');
$router->add('DELETE', '/suppliers/:id', 'SupplierController@delete');


// Purchase Orders routers
$router->add('GET', '/purchase-orders', 'PurchaseOrderController@getAll');
$router->add('GET', '/purchase-orders/:id', 'PurchaseOrderController@getById');
$router->add('POST', '/purchase-orders', 'PurchaseOrderController@create');
$router->add('PUT', '/purchase-orders/:id', 'PurchaseOrderController@update');
$router->add('PATCH', '/purchase-orders/:id', 'PurchaseOrderController@update');
$router->add('DELETE', '/purchase-orders/:id', 'PurchaseOrderController@delete');

// Products routers
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
// Dashboard Router
$router->add('GET', '/dashboard/summary', 'DashboardController@getSummary');
$router->add('GET', '/dashboard', 'DashboardController@getSummary');


$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) { // remove query string to avoid errors on router
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);


$scriptName = $_SERVER['SCRIPT_NAME']; 
$basePath = str_replace('index.php', '', $scriptName);
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

if (strpos($uri, 'index.php') === 0) {
    $uri = substr($uri, strlen('index.php'));
}

if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

$uri = preg_replace('/\/+/', '/', $uri);

$router->dispatch($uri, $method);