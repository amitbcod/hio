<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('generate_jwt')) {
    function generate_jwt($payload) {
        $secretKey = 'your_secret_key'; // Replace with a secure key
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $expirationTime;

        return JWT::encode($payload, $secretKey, 'HS256');
    }
}

if (!function_exists('verify_jwt')) {
    function verify_jwt($token) {
        $secretKey = 'your_secret_key'; // Same key used for encoding
        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
