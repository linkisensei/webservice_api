<?php namespace webservice_api\services;

use \webservice_api\config;
use \webservice_api\value_objects\auth\jwt_token;

final class jwt_token_service {

    private config $config;

    public function __construct(){
        $this->config = config::instance();
    }

    public function generate_access_token(object $user, array $payload = []) : jwt_token {
        return jwt_token::create(
            $user,
            $this->config->get_jwt_access_token_secret(),
            $this->config->get_jwt_access_token_ttl(),
            $payload
        );
    }

    public function parse_access_token(string $token) : jwt_token {
        return jwt_token::parse($token, $this->config->get_jwt_access_token_secret());
    }

    public function generate_refresh_token(object $user, array $payload = []) : jwt_token {
        return jwt_token::create(
            $user,
            $this->config->get_jwt_refresh_token_secret(),
            $this->config->get_jwt_refresh_token_ttl(),
            $payload
        );
    }

    public function parse_refresh_token(string $token) : jwt_token {
        return jwt_token::parse($token, $this->config->get_jwt_refresh_token_secret());
    }
}
