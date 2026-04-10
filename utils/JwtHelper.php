<?php
require_once __DIR__ . '/../config/config.php';

class JwtHelper {
    public static function encode($payload) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;

        $base64Header = self::base64UrlEncode(json_encode($header));
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    public static function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        $expectedSignature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
        $expectedBase64Signature = self::base64UrlEncode($expectedSignature);

        if (!hash_equals($base64Signature, $expectedBase64Signature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        if (!$payload || !isset($payload['exp'])) {
            return false;
        }

        if (time() > $payload['exp']) {
            return false;
        }

        return (object) $payload;
    }

    public static function refresh($token) {
        $decoded = self::decode($token);
        if (!$decoded) {
            return false;
        }

        $newPayload = [
            'id' => $decoded->id,
            'email' => $decoded->email ?? null
        ];

        return self::encode($newPayload);
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}