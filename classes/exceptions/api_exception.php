<?php namespace webservice_api\exceptions;

use \League\Route\Http\Exception\HttpExceptionInterface;

use \Psr\Http\Message\ResponseInterface;
use \Exception;
use \Throwable;
class api_exception extends Exception implements HttpExceptionInterface {

    protected array $headers = [];
    protected int $status = 500;
    protected array $debug = [];

    public function __construct(string $message, int $status = 500, ?\Throwable $previous = null){
        parent::__construct($message, $status, $previous);
        $this->setStatusCode($status);
    }

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

        return $response->withStatus($this->status);
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

    /**
     * Sets debug information
     *
     * @param array $other
     * @return static
     */
    public function setDebugInfo(array $info) : static {
        $this->debug = $info;
        return $this;
    }

    /**
     * From moodle string
     *
     * @param string $errorcode
     * @param string $module
     * @param string $a
     * @return static
     */
    public static function fromString(string $errorcode, string $module = '', string $a = '') : static {
        return new static(get_string($errorcode, $module, $a));
    }

    /**
     * From webservice_api string
     *
     * @param string $errorcode
     * @param string $a
     * @return static
     */
    public static function fromApiString(string $errorcode, string $a = '') : static {
        return new static(get_string($errorcode, 'webservice_api', $a));
    }

    /**
     * Creates a new instance from another Exception
     *
     * @param \Exception $ex
     * @param boolean $addAsPrevious
     * @return static
     */
    public static function fromException(\Exception $ex, bool $addAsPrevious = false) : static {
        $previous = $addAsPrevious ? $ex : null;

        if($ex instanceof \League\Route\Http\Exception){
            $instance = new static($ex->getMessage(), $ex->getStatusCode(), $previous);
            $instance->setHeaders($ex->getHeaders());
            return $instance;
        }

        return new static($ex->getMessage(), 500, $previous);
    }
}