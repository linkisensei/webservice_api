<?php namespace webservice_api\routing;

use \Closure;
use \League\Route\Router;

class route_manager {
    private static array $route_callbacks = [];

    public static function register(Closure $callback) {
        self::$route_callbacks[] = $callback;
    }

    public static function register_file(string $path) {
        if(!file_exists($path)){
            return;
        }

        self::$route_callbacks[] = function(Router $router) use ($path){
            require_once($path);
        };
    }

    public static function register_from_function_callbacks(){
        foreach (get_plugins_with_function('webservice_api_register_routes') as $callback) {
            self::$route_callbacks[] = $callback; 
        };

    }

    public static function apply_routes(Router $router) {
        foreach (self::$route_callbacks as $callback) {
            $callback($router);
        }
    }
}