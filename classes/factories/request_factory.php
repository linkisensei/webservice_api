<?php namespace webservice_api\factories;

use \moodle_url;
use \Psr\Http\Message\ServerRequestInterface;
use \Laminas\Diactoros\ServerRequestFactory;

class request_factory {

    public static function from_globals() : ServerRequestInterface {
        $request = ServerRequestFactory::fromGlobals();
        return self::patch_uri_path($request);
    }

    protected static function patch_uri_path(ServerRequestInterface $request) : ServerRequestInterface {
        $root = new moodle_url('/local/api');
        $uri = $request->getUri()->getPath();
        $uri = '/' . ltrim(substr($uri, strlen($root->get_path())), '/');
        return $request->withUri($request->getUri()->withPath($uri));
    }
}
