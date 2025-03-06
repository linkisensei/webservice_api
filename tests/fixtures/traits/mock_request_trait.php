<?php namespace webservice_api\fixtures\traits;

require_once(__DIR__ . '/../../../vendor/autoload.php');

use \Laminas\Diactoros\ServerRequest;
use \Laminas\Diactoros\StreamFactory;

trait mock_request_trait {
    
    public function make_request(string $method, string $uri, array $body = [], array $params = [], array $headers = []) : ServerRequest {
        $stream_factory = new StreamFactory();
        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        return new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: $uri,
            method: $method,
            body: $stream_factory->createStream(json_encode($body)),
            headers: $headers,
            cookieParams: [],
            queryParams: [],
            parsedBody: $body,
            protocol: '1.1'
        );
    }
}