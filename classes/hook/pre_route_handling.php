<?php namespace webservice_api\hook;

use \League\Route\Router;

if(!interface_exists('core\hook\described_hook', true)){
    interface described_hook {
        public static function get_hook_description(): string;
        public static function get_hook_tags(): array;
    }
    class_alias('webservice_api\hook\described_hook', 'core\hook\described_hook');
}

final class pre_route_handling implements \core\hook\described_hook {
    protected Router $router;
        
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public static function get_hook_description(): string {
        return 'Hook dispatched during the router initialization. It can be used to register routes and middlewares';
    }

    public static function get_hook_tags(): array {
        return ['api', 'routes'];
    }

    public function get_router(): Router {
        return $this->router;
    }

    public static function get_deprecated_plugin_callbacks(): array {
        return ['webservice_api_register_routes'];
    }
}
