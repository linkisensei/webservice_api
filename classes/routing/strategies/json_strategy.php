<?php namespace webservice_api\routing\strategies;

use \League\Route\Strategy\JsonStrategy;
use \Laminas\Diactoros\ResponseFactory;

use \Psr\Http\Server\MiddlewareInterface;

use \Throwable;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \League\Route\Http\Exception\HttpExceptionInterface;

class json_strategy extends JsonStrategy {
    
    public static function factory() : static {
        return new static(new ResponseFactory());
    }

    /**
     * Overriding the property names in the returned JSON
     *
     * @return MiddlewareInterface
     */
    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse()) implements MiddlewareInterface
        {
            protected $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    $response = $this->response;
                    
                    if (is_a($exception, HttpExceptionInterface::class, true)) {
                        return $exception->buildJsonResponse($response);
                    }

                    $status = 500;

                    if($exception instanceof \required_capability_exception){
                        $status = 401;
                    }

                    $response->getBody()->write(json_encode([
                        'status'   => $status,
                        'message' => $exception->getMessage()
                    ]));

                    $response = $response->withAddedHeader('content-type', 'application/json');
                    return $response->withStatus($status);
                }
            }
        };
    }
}
