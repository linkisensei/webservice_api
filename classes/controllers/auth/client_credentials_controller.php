<?php namespace webservice_api\controllers\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\controllers\abstract_controller;
use \webservice_api\services\oauth2_credentials_service;
use \webservice_api\factories\resources\hal_resource_factory;
use \OpenApi\Attributes as OA;
use \lang_string;

#[OA\Tag(name: "OAuth2 Credentials", description: new lang_string("docs:oauth2_credentials_description", "webservice_api"))] 
class client_credentials_controller extends abstract_controller {

    protected oauth2_credentials_service $service;

    public function __construct(){
        parent::__construct();
        $this->service = new oauth2_credentials_service();
    }

    #[OA\Post(
        path: "/oauth/credentials",
        summary: new lang_string("docs:create_credentials_summary", "webservice_api"),
        description: new lang_string("docs:create_credentials_description", "webservice_api"),
        tags: ["OAuth2 Credentials"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 123),
                    new OA\Property(property: "expires_at", type: "integer", example: 1700000000, description: new lang_string("docs:expires_at_description", "webservice_api"))
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: new lang_string("docs:create_credentials_success", "webservice_api"),
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "client_id", type: "string", example: "client-id"),
                        new OA\Property(property: "client_secret", type: "string", example: "client-secret"),
                        new OA\Property(property: "expires_at", type: "integer", example: 1700000000)
                    ]
                )
            ),
            new OA\Response(response: 400, description: new lang_string("docs:invalid_parameters", "webservice_api"))
        ]
    )]
    public function create_credentials(ServerRequestInterface $request){
        $body = $request->getParsedBody();

        $user_id = $this->required_param($body, 'user_id', PARAM_INT);
        $expires_at = $this->optional_param($body, 'expires_at', PARAM_INT, 0);

        $credentials = $this->service->generate_credentials($user_id, $expires_at);
        return hal_resource_factory::make_oauth2_credentials_resource($credentials);
    }

    #[OA\Patch(
        path: "/oauth/credentials/{client_id}",
        summary: new lang_string("docs:update_credentials_summary", "webservice_api"),
        description: new lang_string("docs:update_credentials_description", "webservice_api"),
        tags: ["OAuth2 Credentials"],
        parameters: [
            new OA\Parameter(name: "client_id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "expires_at", type: "integer", example: 1700000000, description: new lang_string("docs:expires_at_description", "webservice_api"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: new lang_string("docs:update_credentials_success", "webservice_api")),
            new OA\Response(response: 400, description: new lang_string("docs:invalid_parameters", "webservice_api")),
            new OA\Response(response: 404, description: new lang_string("docs:credentials_not_found", "webservice_api"))
        ]
    )]
    public function update_credentials(ServerRequestInterface $request, array $args = []){
        $body = $request->getParsedBody();

        $client_id = $this->required_param($args, 'client_id', PARAM_ALPHANUMEXT);
        $expires_at = $this->optional_param($body, 'expires_at', PARAM_INT, 0);

        $credentials = $this->service->regenerate_credentials($client_id, $expires_at);
        return hal_resource_factory::make_oauth2_credentials_resource($credentials);
    }

    #[OA\Delete(
        path: "/oauth/credentials/{client_id}",
        summary: new lang_string("docs:delete_credentials_summary", "webservice_api"),
        description: new lang_string("docs:delete_credentials_description", "webservice_api"),
        tags: ["OAuth2 Credentials"],
        parameters: [
            new OA\Parameter(name: "client_id", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: new lang_string("docs:delete_credentials_success", "webservice_api")),
            new OA\Response(response: 404, description: new lang_string("docs:credentials_not_found", "webservice_api"))
        ]
    )]
    public function delete_credentials(ServerRequestInterface $request, array $args = []){
        $client_id = $this->required_param($args, 'client_id', PARAM_ALPHANUMEXT);

        $credentials = $this->service->revoke_credentials($client_id);
        return hal_resource_factory::make_oauth2_credentials_resource($credentials);
    }


}
