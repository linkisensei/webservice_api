<?php namespace local_api\value_objects\auth;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \InvalidArgumentException;

class jwt_token {
    private const ALGORITHM = 'HS256';

    private function __construct(
        private readonly string $token,
        private readonly object $payload
    ) {}

    public static function create(object $user, string $secret, int $ttl, array $payload = []): static {
        if (!isset($user->id) || !is_numeric($user->id)) {
            throw new InvalidArgumentException('User object must have a valid ID.');
        }

        $payload['sub'] = (int) $user->id;
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;

        $token = JWT::encode($payload, $secret, self::ALGORITHM);
        return new self($token, (object) $payload);
    }

    public static function parse(string $token, string $secret): static {
        try {
            $decoded = JWT::decode($token, new Key($secret, self::ALGORITHM));

            if (!isset($decoded->sub) || !is_int($decoded->sub)) {
                throw new InvalidArgumentException('Invalid token structure.');
            }

            return new self($token, $decoded);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid or expired token.');
        }
    }

    public function get_user_id(): int {
        return intval($this->payload->sub);
    }

    public function get_token(): string {
        return $this->token;
    }

    public function __toString(): string {
        return $this->get_token();
    }
}