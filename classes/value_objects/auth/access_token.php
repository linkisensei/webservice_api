<?php namespace local_api\value_objects\auth;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \InvalidArgumentException;

final class access_token {
    private static string $secret;
    private static int $ttl;

    private const ALGORITHM = 'HS256';

    private function __construct(
        private readonly string $token,
        private readonly object $payload
    ) {}

    public static function create(object $user): self {
        self::init();

        if (!isset($user->id) || !is_int($user->id)) {
            throw new InvalidArgumentException('User object must have a valid ID.');
        }

        $payload = (object) [
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + self::$ttl
        ];

        $token = JWT::encode($payload, self::$secret, self::ALGORITHM);
        return new self($token, $payload);
    }

    public static function parse(string $token): self {
        self::init();

        try {
            $decoded = JWT::decode($token, new Key(self::$secret, self::ALGORITHM));

            if (!isset($decoded->sub) || !is_int($decoded->sub)) {
                throw new InvalidArgumentException('Invalid token structure.');
            }

            return new self($token, $decoded);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid or expired token.');
        }
    }

    public function get_user(): object {
        global $DB;
        return $DB->get_record('user', ['id' => $this->payload->sub], '*', MUST_EXIST);
    }

    public function get_token(): string {
        return $this->token;
    }

    public function __toString(): string {
        return $this->get_token();
    }

    protected static function init(): void {
        if (!isset(self::$secret)) {
            self::$secret = get_config('local_api', 'jwt_secret');
            self::$ttl = get_config('local_api', 'jwt_ttl') ?: HOURSECS;
        }
    }
}