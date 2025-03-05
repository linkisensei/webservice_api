<?php

$router->post('/auth/token', [\local_api\controllers\auth\auth_controller::class, 'create_token']);

$router->get('/me', [\local_api\controllers\user_controller::class, 'get_current_user'])->middleware(new \local_api\http\middlewares\auth\jwt_token_auth());