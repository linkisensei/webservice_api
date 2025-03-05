<?php namespace local_api\value_objects\auth;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \InvalidArgumentException;
use \local_api\value_objects\auth\access_token;

class refresh_token extends access_token {
    private static string $secret;
    private static int $ttl;

    protected static function init(): void {
        if (!isset(self::$secret)) {
            self::$secret = get_config('local_api', 'jwt_refresh_secret');
            self::$ttl = get_config('local_api', 'jwt_refresh_ttl') ?: HOURSECS;
        }
    }
}