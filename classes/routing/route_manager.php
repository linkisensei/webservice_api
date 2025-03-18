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
            require($path);
        };
    }

    public static function register_local_routes(Router $router){
        global $CFG;
        require($CFG->dirroot . '/webservice/api/routes.php');
    }

    public static function register_from_function_callbacks(){
        foreach (self::get_plugins_with_api_register_routes_function() as $callback) {
            self::$route_callbacks[] = $callback; 
        };
    }

    protected static function get_plugins_with_api_register_routes_function(): array {
        $callbacks = [];
    
        $plugins = get_plugins_with_function('webservice_api_register_routes');
        array_walk_recursive($plugins, function ($callback) use (&$callbacks) {
            $callbacks[] = $callback;
        });
    
        return $callbacks;
    }
   

    public static function register_from_hook(Router $router){
        $hook = new \webservice_api\hook\pre_route_handling($router);
        \core\hook\manager::get_instance()->dispatch($hook);
    }

    public static function apply_routes(Router $router) {
        foreach (self::$route_callbacks as $callback) {
            $callback($router);
        }

        if(class_exists('core\hook\manager')){
            self::register_from_hook($router);
        }
    }
}