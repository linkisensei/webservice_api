<?php namespace local_api\middlewares;

use \webservice;
use \moodle_exception;
use \context_system;
use \Psr\Http\Message\ServerRequestInterface;
use \local_api\middlewares\abstract_auth;
use \local_api\exceptions\auth_failure_exception;


class permanent_token_auth extends abstract_auth {

    protected function get_authenticated_user(ServerRequestInterface $request) : ?object {
        global $DB;

        $bearer_token = $this->get_bearer_token($request);

        $other = [
            'method' => WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN,
        ];

        if(!$bearer_token || !$token = $DB->get_record('external_tokens', ['token' => $bearer_token])){
            throw new auth_failure_exception('invalidtoken', 'webservice', 'invalid_token', $other);
        }

        if($token->validuntil and $token->validuntil < time()){
            $DB->delete_records('external_tokens', ['id' => $token->id]);
            throw new auth_failure_exception('accessexception', 'webservice', 'expired_token');
        }

        if($token->sid){
            // Assumes that if sid is set then there must be a valid associated session no matter the token type
            if (!\core\session\manager::session_exists($token->sid)){
                $DB->delete_records('external_tokens', ['sid'=>$token->sid]);
                throw new auth_failure_exception('accessexception', 'webservice', 'invalid_session');
            }
        }

        webservice::update_token_lastaccess($token);

        return $DB->get_record('user', ['id'=>$token->userid], '*') ?: null;
    }
    
}
