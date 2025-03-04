<?php

$router->post('/auth/token', [\local_api\controllers\auth_controller::class, 'create_token']);