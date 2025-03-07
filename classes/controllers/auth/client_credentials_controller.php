<?php namespace webservice_api\controllers\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\controllers\abstract_controller;
use \webservice_api\services\client_credentials_service;
use \webservice_api\http\response\resources\hal_resource;
use \webservice_api\http\response\transformers\entities\compact_user_transformer;
use \webservice_api\exceptions\api_exception;
use \webservice_api\factories\resources\hal_resource_factory;
use \webservice_api\helpers\routing\api_route_helper;

class client_credentials_controller extends abstract_controller {
    

    protected client_credentials_service $service;

    public function __construct(){
        $this->service = new client_credentials_service();
    }

    public function create_client(ServerRequestInterface $request){
        $body = $request->getParsedBody();

        $userid = $this->required_param($body, 'userid', PARAM_INT);
        $client = $this->service->create_client($userid);

        $resource = hal_resource_factory::make_oauth_client_resource($client);

        $user = (new compact_user_transformer())->transform($client->get_user());
        $user_resource = hal_resource_factory::make_user_resource($user);
        $resource->embed('user', $user_resource);

        return $resource;
    }

    public function get_client(ServerRequestInterface $request, array $args = []){
        $clientid = $this->required_param($args, 'clientid', PARAM_TEXT);

        $client = $this->service->get_client($clientid);
        $resource = hal_resource_factory::make_oauth_client_resource($client);

        $user = (new compact_user_transformer())->transform($client->get_user());
        $user_resource = hal_resource_factory::make_user_resource($user);
        $resource->embed('user', $user_resource);

        return $resource;
    }

    public function delete_client(ServerRequestInterface $request, array $args = []){
        $clientid = $this->required_param($args, 'clientid', PARAM_TEXT);
        $client = $this->service->delete_client($clientid);

        $resource = new hal_resource();
        $resource->embed('client', [
            'id' => $client->get('clientid'),
        ]);
        return $resource;
    }

    public function list_client_secrets(ServerRequestInterface $request, array $args = []){
        $clientid = $this->required_param($args, 'clientid', PARAM_TEXT);

        $client = $this->service->get_client($clientid);
        $client_secrets = $this->service->list_client_secrets($client);
        $client_secrets = array_map([hal_resource_factory::class, 'make_oauth_client_resource'], $client_secrets);

        $resource = new hal_resource(['clientid' => $clientid]);
        $resource->embed('client', hal_resource_factory::make_oauth_client_resource($client));
        $resource->embed('secrets', $client_secrets);

        return $resource;
    }

    public function create_client_secret(ServerRequestInterface $request, array $args = []){
        $body = $request->getParsedBody();

        $clientid = $this->required_param($args, 'clientid', PARAM_TEXT);
        $name = $this->optional_param($body, 'name', PARAM_TEXT, '');
        $valid_until = $this->optional_param($body, 'validuntil', PARAM_INT, 0);

        $client_secret = $this->service->create_client_secret($clientid, $name, $valid_until);
        $resource = hal_resource_factory::make_oauth_client_secret_resource($client_secret);

        return $resource;
    }

    public function get_client_secret(ServerRequestInterface $request, array $args = []){
        $clientid = $this->required_param($args, 'clientid', PARAM_TEXT);
        $secretid = $this->required_param($args, 'secretid', PARAM_TEXT);

        $client_secret = $this->service->get_client_secret($clientid, $secretid);
        $resource = hal_resource_factory::make_oauth_client_secret_resource($client_secret);

        return $resource;
    }

    public function delete_client_secret(ServerRequestInterface $request, array $args = []){
        $clientid = $this->required_param($args, 'clientid', PARAM_TEXT);
        $secretid = $this->required_param($args, 'secretid', PARAM_TEXT);
    }
}
