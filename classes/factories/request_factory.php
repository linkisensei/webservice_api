<?php namespace webservice_api\factories;

use \moodle_url;
use \Psr\Http\Message\ServerRequestInterface;
use \Laminas\Diactoros\ServerRequestFactory;
use \webservice_api\helpers\routing\api_route_helper;

class request_factory {
    

    public static function from_globals() : ServerRequestInterface {
        $request = ServerRequestFactory::fromGlobals();

        if(empty($request->getParsedBody()) && str_contains($request->getHeaderLine('Content-Type'), '/json')) {
            $rawBody = (string) $request->getBody();
            $parsedBody = json_decode($rawBody, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($parsedBody);
            }
        }

        return self::patch_uri_path($request);
    }

    protected static function patch_uri_path(ServerRequestInterface $request) : ServerRequestInterface {
        $uri = api_route_helper::get_api_relative_uri($request->getUri()->getPath());
        return $request->withUri($request->getUri()->withPath($uri));
    }
}
