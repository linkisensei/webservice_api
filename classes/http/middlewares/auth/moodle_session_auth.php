<?php namespace webservice_api\http\middlewares\auth;

use \webservice_api\exceptions\auth_failure_exception;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;


class moodle_session_auth extends MiddlewareInterface {

    protected $accept_guest = false;
    protected $redirect_to_login = false;

    public function accept_guest_access() : static {
        $this->accept_guest = true;
        return $this;
    }

    public function redirect_to_login_page() : static {
        $this->redirect_to_login = true;
        return $this;
    }

    protected function validate_user(){
        if(!isloggedin()){
            throw auth_failure_exception::fromApiString('requireloginerror', 'error')->setReason('no_moodle_session');
        }
        
        if(!$this->accept_guest && isguestuser()){
            throw auth_failure_exception::fromApiString('requireloginerror', 'error')->setReason('guest_user_not_allowed');
        }        
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        global $SESSION;

        try {
            $this->validate_user();
            return $handler->handle($request);

        } catch (auth_failure_exception $ex) {
            $ex->toEvent()->trigger();
            
            if($this->redirect_to_login){
                $SESSION->wantsurl = qualified_me();
                redirect(get_login_url());
                exit();
            }

            throw $ex;
        }
    }
}
