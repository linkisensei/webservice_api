<?php

use \League\Route\RouteGroup;
use \webservice_api\controllers\auth\client_credentials_controller;
use \webservice_api\controllers\auth\oauth2_controller;
use \webservice_api\http\middlewares\auth\oauth2_token_auth;
use \webservice_api\http\middlewares\log\request_logger;
use \webservice_api\controllers\user_controller;
use \webservice_api\controllers\openapi_controller;

// Setting CORS to all routes
$router->middleware(new \webservice_api\http\middlewares\cors_middleware());

// Auth routes
$router->post('/oauth2/token', [oauth2_controller::class, 'issue_token']);

// Auth clients and credentials
$router->group('/oauth2/credentials', function (RouteGroup $route) {
    $route->post('/', [client_credentials_controller::class, 'create_credentials']);
    $route->patch('/{client_id}', [client_credentials_controller::class, 'update_credentials']);
    $route->delete('/{client_id}', [client_credentials_controller::class, 'delete_credentials']);
})->middleware(new oauth2_token_auth());

// Current user routes
$router->get('/me', [user_controller::class, 'get_current_user'])
    ->middleware(new oauth2_token_auth())
    ->middleware(new request_logger());

// Documentation
$router->get('/docs/openapi.{format}', [openapi_controller::class, 'get_openapi_file']);