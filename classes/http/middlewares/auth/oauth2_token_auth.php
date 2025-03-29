<?php namespace webservice_api\http\middlewares\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\http\middlewares\auth\abstract_auth;
use \webservice_api\exceptions\auth_failure_exception;

use \Exception;
use \Firebase\JWT\BeforeValidException;
use \Firebase\JWT\ExpiredException;

use \webservice_api\services\oauth2_token_service;

class oauth2_token_auth extends abstract_auth {

    protected $token_service;

    public function __construct(){
        $this->token_service = new oauth2_token_service();
    }

    protected function get_authenticated_user(ServerRequestInterface $request) : ?object {
        global $DB;

        try {
            $token = $this->token_service->parse_access_token($this->get_bearer_token($request) ?: '');
            return $DB->get_record('user', ['id' => $token->get_user_id()]) ?: null;

        } catch (BeforeValidException|ExpiredException $ex) {
            throw auth_failure_exception::fromString('exception:invalid_timed_token', 'webservice_api')->setReason('expired_token');
        } catch (Exception $ex){
            throw auth_failure_exception::fromString('exception:invalid_token', 'webservice_api')->setReason('invalid_token');
        }
    }
}
