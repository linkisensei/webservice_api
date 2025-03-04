<?php namespace local_api\exceptions;

use \League\Route\Http\Exception\HttpExceptionInterface;

use \Psr\Http\Message\ResponseInterface;

class api_exception extends \moodle_exception implements HttpExceptionInterface {

    protected array $headers = [];
    protected int $status = 500;

    public function buildJsonResponse(ResponseInterface $response): ResponseInterface {
        $this->headers['content-type'] = 'application/json';

        foreach ($this->headers as $key => $value) {
            /** @var ResponseInterface $response */
            $response = $response->withAddedHeader($key, $value);
        }

        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                'status' => $this->status,
                'message' => $this->message
            ]));
        }

        return $response->withStatus($this->status, $this->message);
    }

    public function setHeaders(array $headers) : static {
        $this->headers = $headers;
        return $this;
    }

    public function setStatusCode(int $status) : static {
        $this->status = $status;
        return $this;
    }

    public function getStatusCode(): int {
        return $this->status;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public static function factory(...$args) : static {
        return new static(...$args);
    }
}