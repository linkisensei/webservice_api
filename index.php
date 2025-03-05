<?php declare(strict_types=1);

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/../../config.php');

// Exception Handler
set_exception_handler(function(Throwable $th){
    global $CFG;
    
    $info = [
        'status' => 500,
        'message' => $th->getMessage(),
    ];

    if($CFG->debugdeveloper){
        $info['file'] = $th->getFile();
        $info['line'] = $th->getLine();
        $info['trace'] = $th->getTrace();
    }

    if(is_a($th, \League\Route\Http\Exception\HttpExceptionInterface::class, true)){
        $info['status'] = $th->getStatusCode();

        foreach ($th->getHeaders() as $key => $value) {
            header("$key: $value");
        }
    }

    http_response_code($info['status']);
    echo json_encode($info);
    exit();
});

// Assemblying request
$request = \local_api\factories\request_factory::from_globals();

// Initiating router
$router = new \League\Route\Router;

// Setting JSON Strategy
$router->setStrategy(\local_api\routing\strategies\json_strategy::factory());

// Loading routes
require_once(__DIR__ . '/routes.php');

// Loading other plugins routes
\local_api\routing\route_manager::apply_routes($router);

// Responding to browser
$emmiter = new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
$emmiter->emit($router->handle($request));