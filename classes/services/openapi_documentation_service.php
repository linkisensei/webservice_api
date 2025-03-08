<?php namespace webservice_api\services;

use \webservice_api\routing\route_manager;
use \webservice_api\routing\adapters\external_api_adapter;
use \OpenApi\Generator;
use \League\Route\Router;
use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionFunction;
use \Closure;

class openapi_documentation_service {

    const FORMAT_JSON = 'json';
    const FORMAT_YAML = 'yaml';

    public function generate_and_serve(string $format = self::FORMAT_JSON): string {
        $this->set_content_type($format);
        echo $this->generate($format);
        exit();
    }

    protected function set_content_type(string $format = self::FORMAT_JSON){
        switch ($format) {
            case self::FORMAT_YAML:
                header('Content-Type: application/x-yaml');
                header('Content-Disposition: attachment; filename="openapi.yaml"');
                break;
            case self::FORMAT_JSON:
            default:
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="openapi.json"');
        }
    }

    public function generate(string $format = self::FORMAT_JSON): string {
        raise_memory_limit(MEMORY_HUGE);

        $router = $this->initialize_router();
        $routes = $this->get_all_routes($router);

        $controllers = [];

        foreach ($routes as $route) {
            $this->resolve_and_append_file($controllers, $route->getCallable());
        }

        $openapi = @Generator::scan(array_keys($controllers));

        switch ($format) {
            case self::FORMAT_YAML:
                return $openapi->toYaml();
                break;
            case self::FORMAT_JSON:
            default:
                return $openapi->toJson();
        }
    }

    protected function initialize_router(): Router {
        global $CFG;

        $router = new Router();

        require_once($CFG->dirroot . '/webservice/api/routes.php');

        route_manager::register_from_function_callbacks();
        route_manager::apply_routes($router);

        return $router;
    }

    protected function get_all_routes(Router $router): array {
        $router_reflection = new ReflectionClass($router);

        $collect_group_routes_method = $router_reflection->getMethod('collectGroupRoutes');
        $collect_group_routes_method->setAccessible(true);
        $collect_group_routes_method->invoke($router);

        $routes_property = new ReflectionProperty($router, 'routes');
        $routes_property->setAccessible(true);

        return $routes_property->getValue($router);
    }

    protected function resolve_and_append_file(array &$controllers, mixed $handler){
        if($handler instanceof Closure){
            return $this->append_closure_file($controllers, $handler);
        }
    
        if(is_string($handler)){
            return $this->append_file_from_class_name($controllers, $handler);
        }

        if(!is_array($handler) || empty($handler[0])){
            return;
        }

        if($handler[0] instanceof external_api_adapter){
            return $this->append_external_api_adapter_file($controllers, $handler[0]);
        }

        if(is_object($handler[0])){
            return $this->append_object_file($controllers, $handler[0]);
        }

        if(is_string($handler[0]) && class_exists($handler[0])){
            return $this->append_file_from_class_name($controllers, $handler[0]);
        }
    }

    protected function append_file_from_class_name(array &$controllers, string $handler){
        if (!class_exists($handler)) {
            return;
        }

        try {
            $reflection = new ReflectionClass($handler);
            $controllers[$reflection->getFileName()] = $handler;
        } catch (\ReflectionException) {
            return;
        }
    }

    protected function append_object_file(array &$controllers, object $handler){
        if(strtolower($handler::class) == 'stdClass'){
            return;
        }

        try {
            $reflection = new ReflectionClass($handler);
            $controllers[$reflection->getFileName()] = $handler::class;
        } catch (\ReflectionException) {
            return null;
        }
    }

    protected function append_external_api_adapter_file(array &$controllers, external_api_adapter $handler){
        return $this->append_file_from_class_name($controllers, $handler->get_class());
    }

    protected function append_closure_file(array &$controllers, Closure $handler){
        $reflection = new ReflectionFunction($handler);
        $controllers[$reflection->getFileName()] = $handler::class;
    }
}
