<?php namespace local_api\middlewares;

use \Psr\Http\Message\ServerRequestInterface;
use \local_api\middlewares\abstract_auth;
use \local_api\exceptions\auth_failure_exception;

use \Exception;
use \Firebase\JWT\BeforeValidException;
use \Firebase\JWT\ExpiredException;

use \local_api\value_objects\auth\access_token;

class jwt_token_auth extends abstract_auth {

    protected function get_authenticated_user(ServerRequestInterface $request) : ?object {
        try {
            $token = access_token::parse($this->get_bearer_token($request) ?: '');
            return $token->get_user();

        } catch (BeforeValidException|ExpiredException $ex) {
            throw auth_failure_exception::fromString('invalidtimedtoken', 'webservice')->setReason('expired_token');
        } catch (Exception $ex){
            throw auth_failure_exception::fromString('invalidtoken', 'webservice')->setReason('invalid_token');
        }
    }
}
