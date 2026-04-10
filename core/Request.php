<?php

class Request {
    private $method;
    private $uri;
    private $headers;
    private $body;
    private $params;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->headers = getallheaders() ?: [];
        $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->params = $_GET;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($key, $default = null) {
        return $this->headers[$key] ?? $this->headers[strtolower($key)] ?? $default;
    }

    public function getBody() {
        return $this->body;
    }

    public function get($key, $default = null) {
        return $this->body[$key] ?? $default;
    }

    public function getParams() {
        return $this->params;
    }

    public function getParam($key, $default = null) {
        return $this->params[$key] ?? $default;
    }

    public function all() {
        return array_merge($this->body, $this->params);
    }

    public function has($key) {
        return isset($this->body[$key]);
    }
}