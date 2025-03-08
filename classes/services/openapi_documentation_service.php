<?php namespace webservice_api\services;

use \webservice_api\helpers\routing\api_route_helper;
use \webservice_api\routing\route_manager;
use \webservice_api\exceptions\api_exception;
use \webservice_api\routing\adapters\external_api_adapter;
use \OpenApi\Generator;
use \League\Route\Router;
use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionFunction;
use \Closure;
use \cache;
use \webservice_api\config;

/**
 * Service responsible for generating OpenAPI documentation for the webservice API.
 * 
 * This class scans the registered API routes, collects relevant controllers,
 * and generates OpenAPI-compliant documentation in JSON or YAML format. It 
 * dynamically appends the API server URL and ensures the documentation remains
 * up to date with the defined routes.
 * 
 * Supported formats:
 * - JSON (`application/json`)
 * - YAML (`application/x-yaml`)
 */
class openapi_documentation_service {

    const FORMAT_JSON = 'json';
    const FORMAT_YAML = 'yaml';

    const CACHE_AREA = 'openapi';

    public function validate_format(string $format){
        if(!in_array($format, [self::FORMAT_JSON, self::FORMAT_YAML])){
            throw api_exception::fromApiString('exception:invalid_openapi_format')->setStatusCode(400);
        }
    }

    public function get_content_type(string $format = self::FORMAT_JSON){
        switch ($format) {
            case self::FORMAT_YAML:
                return 'application/x-yaml';
            case self::FORMAT_JSON:
            default:
                return 'application/json';
        }
    }

    /**
     * Returns the content of a openapi file.
     *
     * @param string $format
     * @return string
     */
    public function get(string $format = self::FORMAT_JSON) : string {
        $this->validate_format($format);

        $cache = cache::make('webservice_api', self::CACHE_AREA);
        
        if($cached = $cache->get($format)){
            return $cached;
        }

        $content = $this->generate($format);
        $cache->set($format, $content);

        return $content;
    }

    protected function generate(string $format = self::FORMAT_JSON): string {
        $previous_language = force_current_language(config::instance()->get_docs_language());

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $router = $this->initialize_router();
        $routes = $this->get_all_routes($router);

        $controllers = [];

        foreach ($routes as $route) {
            $this->resolve_and_append_file($controllers, $route->getCallable());
        }

        $this->invalidate_controllers_opcache($controllers);
        $openapi = @Generator::scan(array_keys($controllers));

        // Appending server
        $openapi->servers = [
            new \OpenApi\Annotations\Server([
                'url' => api_route_helper::get_api_root_uri(),
                'description' => '',
            ])
        ];

        force_current_language($previous_language);

        switch ($format) {
            case self::FORMAT_YAML:
                return $openapi->toYaml();
            case self::FORMAT_JSON:
            default:
                return $openapi->toJson();
        }
    }

    protected function invalidate_controllers_opcache(array $controllers){
        if(!function_exists('opcache_invalidate')){
            return;
        }

        foreach (array_values($controllers) as $filepath) {
            opcache_invalidate($filepath, true);
        }
    }

    protected function initialize_router(): Router {
        global $CFG;

        $router = new Router();

        require($CFG->dirroot . '/webservice/api/routes.php');

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
