<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;

class JwtService
{
    private string $key;
    private string $algo;

    public function __construct()
    {
        $this->key = config('jwt.secret', env('JWT_SECRET', 'change_me_in_production'));
        $this->algo = config('jwt.algo', 'HS256');
    }

    /**
     * Create an access token for a user.
     */
    public function createAccessToken($user, int $minutes = 15): string
    {
        $payload = [
            'sub' => $user->id,
            'uuid' => $user->uuid,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + ($minutes * 60),
        ];

        return JWT::encode($payload, $this->key, $this->algo);
    }

    /**
     * Create a long-lived refresh token.
     */
    public function createRefreshToken($user, int $days = 30): string
    {
        $payload = [
            'sub' => $user->id,
            'uuid' => $user->uuid,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + ($days * 24 * 60 * 60),
        ];

        return JWT::encode($payload, $this->key, $this->algo);
    }

    /**
     * Decode and validate a JWT token.
     */
    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->key, $this->algo));
    }

    /**
     * Check if a token is valid without decoding.
     */
    public function isValid(string $token): bool
    {
        try {
            $this->decode($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the user ID from a token without full validation (for blacklisting).
     */
    public function getUserIdFromToken(string $token): ?int
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            return $payload['sub'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if token is blacklisted.
     */
    public function isBlacklisted(string $token): bool
    {
        $tokenId = $this->getTokenId($token);
        if (!$tokenId) {
            return true;
        }

        return Cache::has('jwt_blacklist:' . $tokenId);
    }

    /**
     * Blacklist a token.
     */
    public function blacklist(string $token, int $ttlMinutes = 60): bool
    {
        $tokenId = $this->getTokenId($token);
        if (!$tokenId) {
            return false;
        }

        Cache::put('jwt_blacklist:' . $tokenId, true, now()->addMinutes($ttlMinutes));
        return true;
    }

    /**
     * Get unique token ID from JWT.
     */
    private function getTokenId(string $token): ?string
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            return md5($payload['sub'] . ($payload['jti'] ?? '') . ($payload['exp'] ?? ''));
        } catch (\Exception $e) {
            return null;
        }
    }
}
