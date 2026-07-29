<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;

class TokenService
{
    public function __construct(private readonly string $secret)
    {
    }

    public function create(User $user): string
    {
        $payload = [
            'sub' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'exp' => (new DateTimeImmutable('+1 day'))->getTimestamp(),
        ];

        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $body, $this->secret, true));

        return $body.'.'.$signature;
    }

    public function parse(string $token): ?array
    {
        [$body, $signature] = array_pad(explode('.', $token, 2), 2, null);
        if (!$body || !$signature) {
            return null;
        }

        $expected = $this->base64UrlEncode(hash_hmac('sha256', $body, $this->secret, true));
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($body), true);
        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
