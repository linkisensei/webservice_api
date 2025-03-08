<?php namespace webservice_api\controllers\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\controllers\abstract_controller;
use \webservice_api\services\oauth_credentials_service;
use \webservice_api\factories\resources\hal_resource_factory;

class client_credentials_controller extends abstract_controller {

    protected oauth_credentials_service $service;

    public function __construct(){
        parent::__construct();
        $this->service = new oauth_credentials_service();
    }

    public function create_credentials(ServerRequestInterface $request){
        $body = $request->getParsedBody();

        $user_id = $this->required_param($body, 'user_id', PARAM_INT);
        $expires_at = $this->optional_param($body, 'expires_at', PARAM_INT, 0);

        $credentials = $this->service->generate_credentials($user_id, $expires_at);
        return hal_resource_factory::make_oauth_credentials_resource($credentials);
    }

    public function update_credentials(ServerRequestInterface $request, array $args = []){
        $body = $request->getParsedBody();

        $client_id = $this->required_param($args, 'client_id', PARAM_ALPHANUMEXT);
        $expires_at = $this->optional_param($body, 'expires_at', PARAM_INT, 0);

        $credentials = $this->service->regenerate_credentials($client_id, $expires_at);
        return hal_resource_factory::make_oauth_credentials_resource($credentials);
    }

    public function delete_credentials(ServerRequestInterface $request, array $args = []){
        $client_id = $this->required_param($args, 'client_id', PARAM_ALPHANUMEXT);

        $credentials = $this->service->revoke_credentials($client_id);
        return hal_resource_factory::make_oauth_credentials_resource($credentials);
    }


}
