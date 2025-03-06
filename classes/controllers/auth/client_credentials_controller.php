<?php namespace webservice_api\controllers\auth;

use \webservice_api\controllers\abstract_controller;
use \webservice_api\services\client_credentials_service;

class client_credentials_controller extends abstract_controller {

    protected client_credentials_service $service;

    public function __construct(){
        $this->service = new client_credentials_service();
    }

    
}
