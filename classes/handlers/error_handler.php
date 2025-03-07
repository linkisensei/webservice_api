<?php namespace webservice_api\handlers;

class error_handler {

    public static function register(int $error_levels = E_ALL): void {
        set_error_handler([self::class, 'handle'], $error_levels);
    }

    public static function handle($errno, $errstr, $errfile, $errline){
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);            
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            default:
                return true; // Use default error handler
        }
        
        
    }
}
