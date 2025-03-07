<?php namespace webservice_api\handlers;

use \Throwable;
use \League\Route\Http\Exception\HttpExceptionInterface;
use \Psr\Http\Message\ResponseInterface;
use \Laminas\Diactoros\Response\XmlResponse;
use \Laminas\Diactoros\Response\JsonResponse;

class exception_handler {
    protected static $response_class = JsonResponse::class;

    public static function register(): void {
        set_exception_handler([self::class, 'handleAndExit']);
    }

    public static function handle(Throwable $th, ?ResponseInterface $response = null): ResponseInterface {
        $info = [
            'status' => 500,
            'message' => $th->getMessage(),
        ];

        if (self::is_debug_enabled()) {
            $info['file'] = $th->getFile();
            $info['line'] = $th->getLine();
            $info['trace'] = $th->getTrace();
        }

        if ($th instanceof HttpExceptionInterface) {
            $info['status'] = $th->getStatusCode();
        }

        if($th instanceof \required_capability_exception){
            $info['status'] = 401;
        }

        return self::make_response($info, $info['status'], $response);
    }

    /**
     * Separated so we can implement other formats than JSON.
     *
     * @param array $data
     * @param integer $status
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    protected static function make_response(array $data, int $status = 500, ?ResponseInterface $response = null) : ResponseInterface {
        $headers = empty($response) ? [] : $response->getHeaders();
        return new self::$response_class($data, $status, $headers);
    }

    public static function handleAndExit(Throwable $th): void {
        $response = self::handle($th);
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $key => $values) {
            foreach ($values as $value) {
                header("$key: $value");
            }
        }
        echo (string) $response->getBody();
        exit();
    }

    protected static function is_debug_enabled(): bool {
        global $CFG;
        return (bool) $CFG->debugdeveloper;
    }
}
