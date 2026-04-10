<?php

class Router {
    private $routes = [];
    private $notFoundHandler;

    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->normalizePath($path),
            'handler' => $handler
        ];
    }

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function setNotFoundHandler($handler) {
        $this->notFoundHandler = $handler;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);
            if ($params !== false) {
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        if ($this->notFoundHandler) {
            $this->executeHandler($this->notFoundHandler);
        } else {
            Response::notFound('Endpoint not found');
        }
    }

    private function normalizePath($path) {
        $path = trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    private function matchPath($pattern, $uri) {
        $patternParts = explode('/', $pattern);
        $uriParts = explode('/', $uri);

        if (count($patternParts) !== count($uriParts)) {
            return false;
        }

        $params = [];

        for ($i = 0; $i < count($patternParts); $i++) {
            if (strpos($patternParts[$i], '{') === 0) {
                $paramName = trim($patternParts[$i], '{}');
                $params[$paramName] = $uriParts[$i];
            } elseif ($patternParts[$i] !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$classOrCallback, $method] = $handler;

            if (is_string($classOrCallback) && class_exists($classOrCallback)) {
                $controller = new $classOrCallback();
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }

            if (is_callable($classOrCallback)) {
                $instance = call_user_func($classOrCallback);
                if (method_exists($instance, $method)) {
                    call_user_func_array([$instance, $method], $params);
                    return;
                }
            }
        }

        Response::error('Handler not found', 500);
    }
}