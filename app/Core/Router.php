<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, $handler, array $middleware = []): self
    {
        $this->routes[] = ['GET', $path, $handler, $middleware];
        return $this;
    }

    public function post(string $path, $handler, array $middleware = []): self
    {
        $this->routes[] = ['POST', $path, $handler, $middleware];
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');

        foreach ($this->routes as [$routeMethod, $routePath, $handler, $middlewares]) {
            if ($method !== $routeMethod) continue;

            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                // Run middlewares
                foreach ($middlewares as $middleware) {
                    $mw = new $middleware();
                    $mw->handle();
                }

                if (is_callable($handler)) {
                    call_user_func_array($handler, $matches);
                } elseif (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $method], $matches);
                }
                return;
            }
        }

        http_response_code(404);
        require_once APP_PATH . '/Views/errors/404.php';
    }
}
