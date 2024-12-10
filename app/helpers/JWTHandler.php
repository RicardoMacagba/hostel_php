<?php
namespace App\Helpers;

class JWTHandler
{
    private $secretKey;
    private $algorithm;

    public function __construct()
    {
        // Get config values
        require_once __DIR__.'/../../config/config.php';

        $this->secretKey =JWT_SECRET_KEY; // Replace with your secret key
        $this->algorithm = 'HS256'; // HMAC with SHA-256
    }

    public function generateToken($payload, $expiry = 3600)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload['exp'] = time() + $expiry; // Add expiration time
        $payloadEncoded = json_encode($payload);

        // Encode to Base64URL
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payloadEncoded);

        // Create signature
        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    public function validateToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $validSignature = hash_hmac('sha256', "$header.$payload", $this->secretKey, true);
        if ($this->base64UrlEncode($validSignature) !== $signature) {
            return false;
        }

        // Decode and check expiration
        $payloadDecoded = json_decode(base64_decode($payload), true);
        if (isset($payloadDecoded['exp']) && $payloadDecoded['exp'] < time()) {
            return false;
        }

        return $payloadDecoded;
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function assignRoomAttributes($model, $params)
{
    $model->room_name = $params['room_name'] ?? null;
    $model->room_type = $params['room_type'] ?? null;
    $model->price = $params['price'] ?? null;
}

}
