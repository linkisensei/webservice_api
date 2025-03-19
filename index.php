<?php declare(strict_types=1);

// define('NO_DEBUG_DISPLAY', true);
define('WS_SERVER', true);

require_once(__DIR__ . '/classes/handlers/error_handler.php');
\webservice_api\handlers\error_handler::register();

// Loading libraries and core
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/../../config.php');

// Exception Handler
\webservice_api\handlers\exception_handler::register();

// Assemblying request
$request = \webservice_api\factories\request_factory::from_globals();

// Initiating router
$router = \webservice_api\factories\router\router_factory::make_router();

// Responding to browser
$emmiter = new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
$emmiter->emit($router->handle($request));