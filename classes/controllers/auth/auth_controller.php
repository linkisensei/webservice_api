<?php namespace webservice_api\controllers\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \moodle_database;
use \webservice_api\exceptions\auth_failure_exception;
use \webservice_api\config;
use \webservice_api\services\jwt_token_service;
use \webservice_api\controllers\abstract_controller;

class auth_controller extends abstract_controller{

    protected moodle_database $db;
    protected $token_service;

    public function __construct() {
        global $DB;
        $this->db = $DB;
        $this->token_service = new jwt_token_service();
    }

    public function create_token(ServerRequestInterface $request) : object {
        $body = $request->getParsedBody();

        if(isset($body['refresh_token'])){
            return $this->refresh_token($request);
        }

        if(empty($body['username'])){
            throw new auth_failure_exception("Empty username", 400);
        }

        if(empty($body['password'])){
            throw new auth_failure_exception("Empty password", 400);
        }
        
        $user = $this->db->get_record('user', [
            'username' => $body['username'],
            'suspended' => 0,
            'deleted' => 0,
        ]);

        if(!$user || !validate_internal_user_password($user, $body['password'])){
            throw new auth_failure_exception("Invalid user credentials", 401);
        }

        $this->validate_user($user);

        return $this->generate_access_token_for_user($user);
    }

    public function refresh_token(ServerRequestInterface $request) : object {
        $body = $request->getParsedBody();

        try {
            $refresh_token = $this->token_service->parse_refresh_token($body['refresh_token'] ?? '');

            $user = $this->db->get_record('user', [
                'id' => $refresh_token->get_user_id(),
                'suspended' => 0,
                'deleted' => 0,
            ]);

            $this->validate_user($user);
    
            return $this->generate_access_token_for_user($user);

        } catch (\Throwable $th) {
            throw new auth_failure_exception('Invalid refresh token', 401);
        }
    }

    protected function validate_user(object $user){
        global $CFG;

        if(!$user->confirmed){
            throw new auth_failure_exception("User not confirmed", 401);
        }

        $has_policy = $CFG->sitepolicy || $CFG->sitepolicyguest;
        if($has_policy && !$user->policyagreed){
            throw new auth_failure_exception("Policy not agreed", 401);
        }
    }

    protected function generate_access_token_for_user(object $user) : object {
        return (object) [
            "access_token" => $this->token_service->generate_access_token($user)->get_token(),
            "refresh_token" => $this->token_service->generate_refresh_token($user)->get_token(),
            "token_type" => "Bearer",
            "expires_in" => config::instance()->get_jwt_access_token_ttl(),
        ];
    }
}
