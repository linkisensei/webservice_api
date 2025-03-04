<?php declare(strict_types=1);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/../../config.php');

// Assemblying request
$request = \local_api\factories\request_factory::from_globals();

// Initiating router
$router = new League\Route\Router;

// Setting JSON Strategy
$router->setStrategy(\local_api\strategy\json_strategy::factory());

// Loading routes
require_once(__DIR__ . '/routes.php');

// Loading other plugins routes
\local_api\route_manager::apply_routes($router);

// Responding to browser
$emmiter = new Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
$emmiter->emit($router->handle($request));