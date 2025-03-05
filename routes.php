<?php

$router->post('/auth/token', [\webservice_api\controllers\auth\auth_controller::class, 'create_token']);

$router->get('/me', [\webservice_api\controllers\user_controller::class, 'get_current_user'])->middleware(new \webservice_api\http\middlewares\auth\jwt_token_auth());