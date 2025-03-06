<?php

// Setting CORS to all routes
$router->middleware(new \webservice_api\http\middlewares\cors_middleware());

// Auth routes
$router->post('/auth/token', [\webservice_api\controllers\auth\auth_controller::class, 'create_token']);

// Current user routes
$router->get('/me', [\webservice_api\controllers\user_controller::class, 'get_current_user'])
    ->middleware(new \webservice_api\http\middlewares\auth\jwt_token_auth())
    ->middleware(new \webservice_api\http\middlewares\log\request_logger());