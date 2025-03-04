<?php namespace local_api\middlewares;

use \context_system;
use \moodle_exception;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Laminas\Diactoros\Response\JsonResponse;
use \local_api\exceptions\auth_failure_exception;

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
     * @param object $user
     * @return void
     */
    protected function validate_user(?object $user){
        global $CFG;

        $other = ['username' => $user->username];

        if(empty($user)){
            throw new auth_failure_exception('accessexception', 'webservice', 'user_not_found');
        }

        // Cannot authenticate unless maintenance access is granted.
        if (!empty($CFG->maintenance_enabled)){
            if(!has_capability('moodle/site:maintenanceaccess', context_system::instance(), $user)){
                throw new moodle_exception('sitemaintenance', 'admin');
            }
        }

        // Deleted users should not be able to call web service
        if (!empty($user->deleted)) {
            throw new auth_failure_exception('wsaccessuserdeleted', 'webservice', 'user_deleted', $other);
        }

        // Only confirmed user should be able to call web service
        if (empty($user->confirmed)) {
            throw new auth_failure_exception('wsaccessuserunconfirmed', 'webservice', 'user_unconfirmed', $other);
        }

        // Check the user is suspended
        if (!empty($user->suspended)) {
            throw new auth_failure_exception('wsaccessusersuspended', 'webservice', 'user_suspended', $other);
        }

        // Retrieve the authentication plugin if no previously done
        if (empty($auth)) {
            $auth  = get_auth_plugin($user->auth);
        }

        // check if credentials have expired
        if (!empty($auth->config->expiration) and $auth->config->expiration == 1) {
            $days2expire = $auth->password_expire($user->username);
            if (intval($days2expire) < 0 ) {
                throw new auth_failure_exception('wsaccessuserexpired', 'webservice', 'password_expired', $other);
            }
        }
    
        // Check if the auth method is nologin (in this case refuse connection)
        if ($user->auth=='nologin'){
            throw new auth_failure_exception('wsaccessusernologin', 'webservice', 'login', $other);
        }
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
                $ex->to_event()->trigger();
            }

            return new JsonResponse(['message' => $ex->getMessage()], 400);
        }
    }

    abstract protected function get_authenticated_user(ServerRequestInterface $request) : ?object;
}
