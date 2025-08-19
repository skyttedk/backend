<?php

class JWT {
    private $secret;

    public function __construct($secret) {
        $this->secret = $secret;
    }

    public function encode($payload, $header = ['alg' => 'HS256', 'typ' => 'JWT']) {
        $payload['iat'] = time();
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + 3600; // Standard udløbstid på 1 time
        }

        $header = $this->base64UrlEncode(json_encode($header));
        $payload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->sign("$header.$payload", $this->secret);
        return "$header.$payload.$signature";
    }

    public function decode($token) {
        list($header, $payload, $signature) = explode('.', $token);
        $headerDecoded = json_decode($this->base64UrlDecode($header), true);
        $payloadDecoded = json_decode($this->base64UrlDecode($payload), true);

        if (!$this->verify("$header.$payload", $signature, $this->secret)) {
            throw new Exception('Ugyldig signatur');
        }

        $this->validateClaims($payloadDecoded);

        return $payloadDecoded;
    }

    private function validateClaims($payload) {
        $currentTime = time();

        if (!isset($payload['iat'])) {
            throw new Exception('Manglende iat claim');
        }

        if ($payload['iat'] > $currentTime) {
            throw new Exception('Token er udstedt i fremtiden');
        }

        if (!isset($payload['exp'])) {
            throw new Exception('Manglende exp claim');
        }

        if ($currentTime >= $payload['exp']) {
            throw new Exception('Token er udløbet');
        }

        // Tilføj yderligere validering her, f.eks. max levetid
        if ($currentTime - $payload['iat'] > 86400) { // 24 timer
            throw new Exception('Token er for gammel');
        }
    }

    private function sign($input, $key) {
        return $this->base64UrlEncode(
            hash_hmac('sha256', $input, $key, true)
        );
    }

    private function verify($input, $signature, $key) {
        $calculatedSignature = $this->sign($input, $key);
        return hash_equals($calculatedSignature, $this->base64UrlDecode($signature));
    }

    private function base64UrlEncode($input) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($input));
    }

    private function base64UrlDecode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $input));
    }
}