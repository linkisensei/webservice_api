<?php namespace webservice_api\http\middlewares\log;

use \Throwable;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \webservice_api\event\api_request_logged;
use \League\Route\Http\Exception\HttpExceptionInterface;

class request_logger implements MiddlewareInterface {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $event = api_request_logged::from_request($request);
        $exception = null;
        $response = null;

        try {
            $response = $handler->handle($request);
            $http_status = $response->getStatusCode();

        } catch (Throwable $th) {
            $exception = $th;
            $http_status = ($th instanceof HttpExceptionInterface) ? $th->getStatusCode() : 500;

        } finally {
            $event->set_http_status($http_status);
            $event->mark_request_end();
            $event->trigger();

            if ($exception) {
                throw $exception;
            }
        }

        return $response;
    }
}
