<?php namespace webservice_api\models\auth;

use \core\persistent;
use \webservice_api\exceptions\api_exception;

class oauth_credentials extends persistent {
    const TABLE = 'webservice_api_credentials';

    protected ?string $secret = null;
    protected ?object $user = null;

    protected static function define_properties() {
        return [
            'client_id' => [
                'type' => PARAM_INT,
            ],
            'user_id' => [
                'type' => PARAM_INT,
            ],
            'secret_hash' => [
                'type' => PARAM_RAW,
            ],
            'expires_at' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'modified_by' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'created_at' => [
                'type' => PARAM_INT,
                'default' => time(),
            ],
            'modified_at' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    /**
     * @throws \webservice_api\exceptions\api_exception
     */
    protected function before_create(){
        global $USER;

        if(empty($this->get('client_id'))){
            $this->raw_set('client_id', self::generate_client_id($this->get('user_id')));
        }

        if(empty($this->secret)){
            $this->generate_secret();
        }

        if(static::record_exists(['client_id' => $this->get('client_id')])){
            throw new api_exception('Credentials already exist for the specified user', 409);
        }
        
        $this->raw_set('created_at', time());
        $this->raw_set('modified_at', time());
        $this->raw_set('modified_by', $USER->id);
    }

    protected function before_update(){
        global $USER;
        $this->raw_set('modified_at', time());
        $this->raw_set('modified_by', $USER->id);
    }

    public static function generate_client_id(int $userid) : string {
        return substr(hash('sha256', $userid), 0, 32);
    }

    public static function hash_secret(string $secret) : string {
        return hash('sha256', $secret);
    }

    public function generate_secret() : static {
        $this->secret = uniqid().bin2hex(random_bytes(16));
        $this->raw_set('secret_hash', static::hash_secret($this->secret));
        return $this;
    }

    public function regenerate_secret(?int $expires_at = null) : static {
        $this->generate_secret();

        if($expires_at !== null){
            $this->raw_set('expires_at', $expires_at);
        }else{
            $this->raw_set('expires_at', 0);
        }

        return $this;
    }

    public function is_expired() : bool {
        $expiration = (int) $this->get('expires_at') ?: INF;
        return $expiration <= time();
    }

    /**
     * Only retriavable when the object is created
     * 
     * Only the hash is stored and recovered from 
     * the database.
     *
     * @return string
     */
    public function get_secret() : ?string {
        return $this->secret;
    }

    /**
     * Returns the related user object, if exists
     *
     * @return object|null
     */
    public function get_user() : ?object {
        global $DB;

        if(empty($this->user)){
            $this->user = $DB->get_record('user', ['id' => $this->get('user_id')]) ?: null;
        }
        return $this->user;
    }

    /**
     * If credentials exist, return an instance of this class
     * 
     * Does not validate if credentials are expired
     *
     * @param string $client_id
     * @param string $client_secret
     * @return static|null
     */
    public static function validate_credentials(string $client_id, string $client_secret) : ?static {
        return static::get_record([
            'client_id' => $client_id,
            'secret_hash' => static::hash_secret($client_secret),
        ]) ?: null;
    }

    public static function get_by_user_id(int $user_id) : ?static {
        return static::get_record([
            'user_id' => $user_id,
        ]);
    }

    public static function get_by_client_id(string $client_id) : ?static {
        return static::get_record([
            'client_id' => $client_id,
        ]);
    }
}
