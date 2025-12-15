<?php
namespace Src\Common;

class Router {
    private $routes = [];

    public function add($method, $path, $controllerAction) { 
        $path = rtrim($path, '/');
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $controllerAction
        ];
    }

    public function dispatch($uri, $method) { 
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        
        foreach ($this->routes as $route) {
            $pattern = "@^" . preg_replace('/:[a-zA-Z0-9]+/', '([a-zA-Z0-9-]+)', $route['path']) . "$@D"; 
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) { 
                array_shift($matches);
                
                list($controller, $action) = explode('@', $route['action']); 
                $controllerClass = "Src\\Controllers\\$controller";
                
                if (class_exists($controllerClass)) {
                    $instance = new $controllerClass();
                    call_user_func_array([$instance, $action], $matches); 
                    return;
                }
            }
        }
        
        Response::error("Endpoint not found", 404);
    }
}