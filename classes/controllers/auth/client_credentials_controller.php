<?php namespace webservice_api\controllers\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\controllers\abstract_controller;
use \webservice_api\services\client_credentials_service;

class client_credentials_controller extends abstract_controller {

    protected client_credentials_service $service;

    public function __construct(){
        $this->service = new client_credentials_service();
    }

    public function list_clients(ServerRequestInterface $request, array $args = []){

    }

    public function create_client(ServerRequestInterface $request){

    }

    public function update_client(ServerRequestInterface $request, array $args = []){

    }

    public function delete_client(ServerRequestInterface $request, array $args = []){

    }

    public function list_client_secrets(ServerRequestInterface $request, array $args = []){

    }

    public function create_client_secret(ServerRequestInterface $request, array $args = []){

    }

    public function get_client_secret(ServerRequestInterface $request, array $args = []){

    }

    public function delete_client_secret(ServerRequestInterface $request, array $args = []){

    }
}
