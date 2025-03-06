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
$router->group('/oauth/client-credentials', function (RouteGroup $route) {
    $route->get('/', [client_credentials_controller::class, 'list_credentials']);
    $route->post('/', [client_credentials_controller::class, 'create_credentials']);
    $route->delete('/{clientid}/{id}', [client_credentials_controller::class, 'delete_credentials']);
    $route->patch('/{clientid}/credentials/{id}/secret', [client_credentials_controller::class, 'update_secret']);
})->middleware(new oauth_token_auth());

// Current user routes
$router->get('/me', [user_controller::class, 'get_current_user'])
    ->middleware(new oauth_token_auth())
    ->middleware(new request_logger());