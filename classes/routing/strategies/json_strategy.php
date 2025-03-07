<?php namespace webservice_api\routing\strategies;

use \League\Route\Strategy\JsonStrategy;
use \Laminas\Diactoros\ResponseFactory;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Throwable;
use \webservice_api\handlers\exception_handler;

class json_strategy extends JsonStrategy {
    
    public static function factory() : static {
        return new static(new ResponseFactory());
    }

    /**
     * Overriding the property names in the returned JSON
     *
     * @return MiddlewareInterface
     */
    public function getThrowableHandler(): MiddlewareInterface {
        return new class ($this->responseFactory->createResponse()) implements MiddlewareInterface {
            protected $response;

            public function __construct(ResponseInterface $response) {
                $this->response = $response;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    return exception_handler::handle($exception, $this->response);
                }
            }
        };
    }
}
