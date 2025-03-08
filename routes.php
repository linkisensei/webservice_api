<?php

use \League\Route\RouteGroup;
use \webservice_api\controllers\auth\client_credentials_controller;
use \webservice_api\controllers\auth\oauth_controller;
use \webservice_api\http\middlewares\auth\oauth_token_auth;
use \webservice_api\http\middlewares\log\request_logger;
use \webservice_api\controllers\user_controller;

// Setting CORS to all routes
$router->middleware(new \webservice_api\http\middlewares\cors_middleware());

// Auth routes
$router->post('/oauth/token', [oauth_controller::class, 'issue_token']);

// Auth clients and credentials
$router->group('/oauth/credentials', function (RouteGroup $route) {
    $route->post('/', [client_credentials_controller::class, 'create_credentials']);
    $route->patch('/{client_id}', [client_credentials_controller::class, 'update_credentials']);
    $route->delete('/{client_id}', [client_credentials_controller::class, 'delete_credentials']);
})->middleware(new oauth_token_auth());

// Current user routes
$router->get('/me', [user_controller::class, 'get_current_user'])
    ->middleware(new oauth_token_auth())
    ->middleware(new request_logger());

//
$router->get('/testing', function($request){
    return ['test' => true];
});

$router->get('/testing2', "\TestController::test");

class TestController{
    public static function test($request){
        return true;
    }
}