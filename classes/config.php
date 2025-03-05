<?php namespace webservice_api;

use \webservice_api\exceptions\api_exception;

/**
 * Singleton config class
 */
final class config {
    const PLUGIN_NAME = 'webservice_api';

    const SETTING_JWT_SECRET = 'jwt_secret';
    const SETTING_JWT_TTL = 'jwt_ttl';
    const SETTING_JWT_REFRESH_SECRET = 'jwt_refresh_secret';
    const SETTING_JWT_REFRESH_TTL = 'jwt_refresh_ttl';

    private static config $instance;
    private object $config;

    public function __construct(){
        $this->reload();
    }

    public function reload(){
        $this->config = get_config(self::PLUGIN_NAME);
    }

    public function get(string $key, $default = null){
        if(isset($this->config->$key)){
            return $this->config->$key;
        }

        return $default;
    }

    public function set(string $key, $value) : static {
        set_config($key, $value, self::PLUGIN_NAME);
        $this->config->$key = $value;
        return $this;
    }

    public static function instance() : static {
        if(isset(self::$instance)){
            return self::$instance;
        }

        return new static();
    }

    public function get_jwt_access_token_secret() : string {
        if($secret = $this->get(self::SETTING_JWT_SECRET)){
            return $secret;
        }

        throw api_exception::fromString('exception:invalid_access_token_secret',self::PLUGIN_NAME);
    }

    public function get_jwt_access_token_ttl() : int {
        return $this->get(self::SETTING_JWT_TTL, HOURSECS);
    }

    public function get_jwt_refresh_token_secret() : string {
        return $this->get(self::SETTING_JWT_REFRESH_SECRET, $this->get_jwt_access_token_secret());
    }

    public function get_jwt_refresh_token_ttl() : int {
        return $this->get(self::SETTING_JWT_REFRESH_TTL, DAYSECS);
    }

    /**
     * To be used on install.php
     * 
     * Generates new authentication secrets
     *
     * @return void
     */
    public static function generate_secrets(){
        $length = 32;
        set_config(self::SETTING_JWT_SECRET, bin2hex(random_bytes($length)), self::PLUGIN_NAME);
        set_config(self::SETTING_JWT_REFRESH_SECRET, bin2hex(random_bytes($length)), self::PLUGIN_NAME);
        
        if(!empty(self::$instance)){
            self::$instance->reload();
        }
    }
}
