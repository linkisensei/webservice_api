<?php declare(strict_types=1);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/../../config.php');

use Psr\Http\Message\ServerRequestInterface;

$request = \local_api\factories\request_factory::from_globals();

$strategy = new League\Route\Strategy\JsonStrategy(new Laminas\Diactoros\ResponseFactory());
$router   = (new League\Route\Router)->setStrategy($strategy);

$router->get('/test',  function (ServerRequestInterface $request): array {
    return [
        'title'   => 'Test',
        'version' => 1,
    ];
});

// map a route
$router->map('GET', '/', function (ServerRequestInterface $request): object {
    return (object) [
        'title'   => 'Home',
        'version' => 1,
    ];
});


$response = $router->handle($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);