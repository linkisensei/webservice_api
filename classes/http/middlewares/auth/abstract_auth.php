<?php namespace webservice_api\http\middlewares\auth;

use \context_system;
use \moodle_exception;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \webservice_api\exceptions\api_exception;
use \webservice_api\exceptions\auth_failure_exception;

abstract class abstract_auth implements MiddlewareInterface {

    /**
     * This function sets up $USER global.
     *
     * @throws moodle_exception
     * @throws auth_failure_exception
     * @param object $user
     * @return void
     */
    protected function login_user(?object $user){
        $this->validate_user($user);

        enrol_check_plugins($user, false);
        \core\session\manager::set_user($user);
        set_login_session_preferences();
    }

    /**
     * Validates if a user can login
     *
     * @throws moodle_exception
     * @throws auth_failure_exception
     * @throws required_capability_exception
     * @param object $user
     * @return void
     */
    protected function validate_user(?object $user){
        global $CFG;

        $other = ['username' => $user->username];

        if(empty($user)){
            throw auth_failure_exception::fromString('accessexception', 'webservice')->setReason('user_not_found')->setOther($other);
        }

        // Cannot authenticate unless maintenance access is granted.
        if (!empty($CFG->maintenance_enabled)){
            if(!has_capability('moodle/site:maintenanceaccess', context_system::instance(), $user)){
                throw api_exception::fromString('sitemaintenance', 'admin');
            }
        }

        // Deleted users should not be able to call web service
        if (!empty($user->deleted)) {
            throw auth_failure_exception::fromString('wsaccessuserdeleted', 'webservice')->setReason('user_deleted')->setOther($other);
        }

        // Only confirmed user should be able to call web service
        if (empty($user->confirmed)) {
            throw auth_failure_exception::fromString('wsaccessuserunconfirmed', 'webservice')->setReason('user_unconfirmed')->setOther($other);
        }

        // Check the user is suspended
        if (!empty($user->suspended)) {
            throw auth_failure_exception::fromString('wsaccessusersuspended', 'webservice')->setReason('user_suspended')->setOther($other);
        }

        // Retrieve the authentication plugin if no previously done
        if (empty($auth)) {
            $auth  = get_auth_plugin($user->auth);
        }

        // check if credentials have expired
        if (!empty($auth->config->expiration) and $auth->config->expiration == 1) {
            $days2expire = $auth->password_expire($user->username);
            if (intval($days2expire) < 0 ) {
                throw auth_failure_exception::fromString('wsaccessuserexpired', 'webservice')->setReason('password_expired')->setOther($other);
            }
        }
    
        // Check if the auth method is nologin (in this case refuse connection)
        if ($user->auth=='nologin'){
            throw auth_failure_exception::fromString('wsaccessusernologin', 'webservice')->setReason('login')->setOther($other);
        }

        require_capability('webservice/api:use', \context_system::instance(), $user);
    }

    protected function get_bearer_token(ServerRequestInterface $request): ?string {
        $authHeader = $request->getHeaderLine('Authorization');
    
        if (preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    
        return null;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        try {
            $user = $this->get_authenticated_user($request);
            $this->login_user($user);
            return $handler->handle($request);

        } catch (\Exception $ex) {
            if($ex instanceof auth_failure_exception){
                $ex->toEvent()->trigger();
            }

            throw $ex;
        }
    }

    abstract protected function get_authenticated_user(ServerRequestInterface $request) : ?object;
}
