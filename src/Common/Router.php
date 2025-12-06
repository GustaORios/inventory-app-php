<?php
namespace Src\Common;

class Router {
    private $routes = [];

    public function add($method, $path, $controllerAction) { // create function to add routers on index.php
        $path = rtrim($path, '/');
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $controllerAction
        ];
    }

    public function dispatch($uri, $method) { // check if uri and method passed matches with $routes[] added
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        
        foreach ($this->routes as $route) {
            $pattern = "@^" . preg_replace('/:[a-zA-Z0-9]+/', '([a-zA-Z0-9-]+)', $route['path']) . "$@D"; // regex pattern @ actiion
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) { // match method and uri
                array_shift($matches);
                
                list($controller, $action) = explode('@', $route['action']); // separate path and @action
                $controllerClass = "Src\\Controllers\\$controller";
                
                if (class_exists($controllerClass)) {
                    $instance = new $controllerClass();
                    call_user_func_array([$instance, $action], $matches); // triggers action passing params
                    return;
                }
            }
        }
        
        Response::error("Endpoint not found", 404);
    }
}