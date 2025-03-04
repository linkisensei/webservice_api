<?php namespace local_api\adapters;

use \Psr\Http\Message\ServerRequestInterface;

use \coding_exception;
use \core_external\external_function_parameters;
use \core_external\external_api;

class external_api_adapter {
    protected string $class;
    protected string $method;
    protected string $parameters_method;

    public function __construct(string $class, string $method){
        if(!class_exists($class)){
            throw new coding_exception("\"$class\" not found!");
        }

        if(!is_a($class, external_api::class, true)){
            throw new coding_exception("\"$class\" must extend \"core_external\\external_api\" not found!");
        }

        if(!method_exists($class, $method)){
            throw new coding_exception("\"$class::$method()\" not found!");
        }

        $this->parameters_method = $method . "_parameters";
        if(!method_exists($class, $this->parameters_method)){
            throw new coding_exception("\"$class::$this->parameters_method()\" not found!");
        }

        $this->class = $class;
        $this->method = $method;
    }

    protected function get_api_parameters_description() : external_function_parameters {
        return call_user_func($this->class, $this->parameters_method);
    }

    protected function server_request_to_api_parameters(ServerRequestInterface $request, array $args) : array {
        $description = $this->get_api_parameters_description();
        $parameters = array_merge($request->getQueryParams(), $args, $request->getParsedBody());
        return external_api::validate_parameters($description, $parameters);
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) {
        $parameters = $this->server_request_to_api_parameters($request, $args);
        return call_user_func_array([$this->class, $this->method], ...$parameters);
    }
}
