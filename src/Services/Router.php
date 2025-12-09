<?php

namespace App\Services;

class Router
{
    private $routes = [];
    private $notFoundHandler;

    public function get($path, $handler, $middleware = [])
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, $middleware = [])
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute($method, $path, $handler, $middleware = [])
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function dispatch($url)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = '/' . trim($url, '/');
        
        if ($url === '/') {
            $url = '/';
        }

        // Log debug info
        $debugLog = __DIR__ . '/../../logs/router_debug.log';
        file_put_contents($debugLog, "[" . date('Y-m-d H:i:s') . "] Dispatch URL: $url, Method: $method\n", FILE_APPEND);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $pattern = $this->convertToRegex($route['path']);
                file_put_contents($debugLog, "  Checking route: {$route['path']} -> Pattern: $pattern\n", FILE_APPEND);
                
                if (preg_match($pattern, $url, $matches)) {
                    file_put_contents($debugLog, "  MATCHED! Matches: " . json_encode($matches) . "\n", FILE_APPEND);
                    file_put_contents($debugLog, "  Executing {$route['handler']}\n", FILE_APPEND);
                    
                    array_shift($matches); // Remove full match
                    
                    file_put_contents($debugLog, "  Final params: " . json_encode($matches) . "\n", FILE_APPEND);
                    
                    // Execute middleware
                    foreach ($route['middleware'] as $middleware) {
                        $middlewareInstance = new $middleware();
                        if (!$middlewareInstance->handle()) {
                            return;
                        }
                    }
                    
                    // Execute handler
                    list($controller, $method) = explode('@', $route['handler']);
                    $controller = "App\\Controllers\\{$controller}";
                    
                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $method)) {
                            call_user_func_array([$controllerInstance, $method], $matches);
                            return;
                        }
                    }
                }
            }
        }

        // 404 Not Found
        if ($this->notFoundHandler) {
            call_user_func($this->notFoundHandler);
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }

    private function convertToRegex($path)
    {
        // Convert route parameters {id} to regex groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function setNotFoundHandler($handler)
    {
        $this->notFoundHandler = $handler;
    }
}
