<?php namespace webservice_api\http\middlewares;

use \Throwable;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \webservice_api\event\api_request_logged;
use \League\Route\Http\Exception\HttpExceptionInterface;
use \Laminas\Diactoros\Response\EmptyResponse;

class cors_middleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new EmptyResponse(200);
        } else {
            $response = $handler->handle($request);
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
    }
}