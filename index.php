<?php declare(strict_types=1);

// define('NO_DEBUG_DISPLAY', true);
define('WS_SERVER', true);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/../../config.php');

// Exception Handler
\webservice_api\handlers\exception_handler::register();

// Assemblying request
$request = \webservice_api\factories\request_factory::from_globals();

// Initiating router
$router = new \League\Route\Router;

// Setting JSON Strategy
$router->setStrategy(\webservice_api\routing\strategies\json_strategy::factory());

// Loading routes
require_once(__DIR__ . '/routes.php');

// Loading other plugins routes
\webservice_api\routing\route_manager::register_from_function_callbacks();
\webservice_api\routing\route_manager::apply_routes($router);

// Responding to browser
$emmiter = new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
$emmiter->emit($router->handle($request));