<?php namespace webservice_api\services;

use \context;
use \webservice_api\config;
use \webservice_api\models\auth\oauth2_credentials;
use \webservice_api\exceptions\auth_failure_exception;
use \webservice_api\exceptions\api_exception;

final class oauth2_credentials_service {
    private config $config;
    private context $context;

    public function __construct(){
        $this->config = config::instance();
        $this->context = \context_system::instance();
    }

    public function get_credentials(string $client_id) : oauth2_credentials {
        if(!$credential = oauth2_credentials::get_by_client_id($client_id)){
            throw api_exception::fromString('exception:client_credentials_not_found', 'webservice_api', 404);
        }
        return $credential;
    }

    /**
     * Validates oauth client credentials
     * 
     * @throws \webservice_api\exceptions\auth_failure_exception
     * @param string $clientid
     * @param string $secret
     * @return client_secret|null
     */
    public function validate_credentials(string $clientid, string $secret) : oauth2_credentials {
        if(!$credential = oauth2_credentials::validate_credentials($clientid, $secret)){
            throw auth_failure_exception::fromString('exception:invalid_client_credentials', 'webservice_api', 401);
        }

        if($credential->is_expired()){
            throw auth_failure_exception::fromString('exception:expired_client_credentials', 'webservice_api', 401);
        }

        return $credential;
    }

    public function generate_credentials(int $user_id, int $expires_at = 0) : oauth2_credentials {
        $this->check_permissions($user_id);

        $credentials = new oauth2_credentials(0, (object) [
            'user_id' => $user_id,
            'expires_at' => $expires_at,
        ]);

        $credentials->save();
        return $credentials;
    }

    public function regenerate_credentials(string $client_id, int $expires_at = 0) : oauth2_credentials {
        $credentials = $this->get_credentials($client_id);
        $this->check_permissions((int) $credentials->get('user_id'));

        if($expires_at <= time()){
            throw api_exception::fromString('exception:invalid_credentials_expiration', 'webservice_api', 400);

        }

        $credentials->regenerate_secret($expires_at);
        $credentials->save();

        return $credentials;
    }

    public function revoke_credentials(string $client_id) : oauth2_credentials {
        $credentials = $this->get_credentials($client_id);

        $this->check_permissions((int) $credentials->get('user_id'));
        $credentials->delete();

        return $credentials;
    }

    /**
     * @throws \required_capability_exception
     * @param integer $user_id Credentials owner
     * @return void
     */
    protected function check_permissions(int $user_id = 0){
        global $USER;

        if($USER->id != $user_id){
            require_capability('webservice/api:managecredentials', $this->context);
        }else{
            require_capability('webservice/api:manageselfcredentials', $this->context);
        }
    }
}
