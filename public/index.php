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

// ==========================================================
// AUTOLOADER CORRIGIDO (FIX CRÍTICO NA LINHA 31)
// ==========================================================
spl_autoload_register(function ($class) {
    $prefix = 'Src\\';
    $base_dir = __DIR__ . '/../src/'; 
    $len = strlen($prefix);
    
    // 1. Verifica o prefixo (Src\)
    if (strncmp($prefix, $class, $len) !== 0) return;
    
    // 2. Remove o prefixo (Ex: Models\Product)
    $relative_class = substr($class, $len);
    
    // 3. CORREÇÃO FINAL: Substitui a ÚNICA barra invertida (\) por barra normal (/)
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php'; 
    
    // 4. Carrega o arquivo
    if (file_exists($file)) require $file;
});


use Src\Common\Router;
use Src\Common\Response;

session_start();

$router = new Router();

// ==========================================================
// ROTAS COMPLETAS
// ==========================================================

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

// Dashboard Router
$router->add('GET', '/dashboard/summary', 'DashboardController@getSummary');
$router->add('GET', '/dashboard', 'DashboardController@getSummary');


$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) { // remove query string to avoid errors on router
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

// Remove a parte do script name da URI para que o router funcione
$scriptName = $_SERVER['SCRIPT_NAME']; 
$basePath = str_replace('index.php', '', $scriptName);
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// -------------------------------------------------------------
// CORREÇÃO CRÍTICA PARA O ERRO 404 NO POSTMAN (Inicio)
// -------------------------------------------------------------

// 1. Remove explicitamente 'index.php' se ele ainda estiver na URI
if (strpos($uri, 'index.php') === 0) {
    $uri = substr($uri, strlen('index.php'));
}

// 2. Garante que a URI comece com uma única barra (/)
if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

// 3. Remove barras duplas (para /suppliers, a URI final será /suppliers)
$uri = preg_replace('/\/+/', '/', $uri);

// -------------------------------------------------------------
// CORREÇÃO CRÍTICA PARA O ERRO 404 NO POSTMAN (Fim)
// -------------------------------------------------------------

$router->dispatch($uri, $method);