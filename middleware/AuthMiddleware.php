<?php
require_once __DIR__ . '/../utils/JwtHelper.php';
require_once __DIR__ . '/../core/Response.php';

class AuthMiddleware {
    public static function verify() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            Response::unauthorized('No token provided');
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || strtolower($parts[0]) !== 'bearer') {
            Response::unauthorized('Invalid token format');
        }

        $token = $parts[1];
        $decoded = JwtHelper::decode($token);

        if (!$decoded) {
            Response::unauthorized('Invalid or expired token');
        }

        return $decoded;
    }

    public static function optional() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            return null;
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2) {
            return null;
        }

        $token = $parts[1];
        return JwtHelper::decode($token);
    }
}