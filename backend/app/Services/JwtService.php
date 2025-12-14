<?php

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JwtService
{
    protected string $algo = 'HS256';

    public function generate(array $payload, int $ttl = null): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => $this->algo]));

        $now = time();
        $ttl = $ttl ?? intval(env('JWT_TTL', 3600));
        $jti = Str::uuid()->toString();

        $defaultPayload = [
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'jti' => $jti,
        ];

        $payload = array_merge($defaultPayload, $payload);

        $body = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->sign("{$header}.{$body}");

        return "{$header}.{$body}.{$signature}";
    }

    public function decode(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid token format');
        }

        [$headerB64, $payloadB64, $signature] = $parts;

        $payloadJson = $this->base64UrlDecode($payloadB64);
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Invalid token payload');
        }

        // verify signature
        $validSig = $this->verify("{$headerB64}.{$payloadB64}", $signature);
        if (!$validSig) {
            throw new \RuntimeException('Invalid token signature');
        }

        return $payload;
    }

    protected function sign(string $data): string
    {
        $secret = (string) env('JWT_SECRET', 'change-this-secret');
        $sig = hash_hmac('sha256', $data, $secret, true);
        return $this->base64UrlEncode($sig);
    }

    protected function verify(string $data, string $signature): bool
    {
        $expected = $this->sign($data);
        return hash_equals($expected, $signature);
    }

    protected function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public function isRevoked(string $jti): bool
    {
        $row = DB::table('jwt_revocations')->where('jti', $jti)->first();
        return (bool) $row;
    }
}
