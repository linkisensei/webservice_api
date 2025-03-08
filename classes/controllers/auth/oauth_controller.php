<?php namespace webservice_api\controllers\auth;

use \Psr\Http\Message\ServerRequestInterface;
use \moodle_database;
use \webservice_api\exceptions\auth_failure_exception;
use \webservice_api\config;
use \webservice_api\services\oauth_token_service;
use \webservice_api\controllers\abstract_controller;
use webservice_api\services\oauth_credentials_service;

use OpenApi\Attributes as OA;


#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "JWT access token must be included in the Authorization header"
)]
class oauth_controller extends abstract_controller{

    protected moodle_database $db;
    protected $token_service;

    const GRANT_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_PASSWORD = 'password';
    const GRANT_REFRESH_TOKEN = 'refresh_token';

    public function __construct() {
        global $DB;
        $this->db = $DB;
        $this->token_service = new oauth_token_service();
    }

    protected function detect_grant_type(ServerRequestInterface $request) : string {
        $body = $request->getParsedBody();

        if(!empty($body['grant_type'])){
            return $body['grant_type'];
        }

        if(isset($body['client_id']) || isset($body['client_secret'])){
            return self::GRANT_CLIENT_CREDENTIALS;
        }

        if(isset($body['username']) || isset($body['password'])){
            return self::GRANT_PASSWORD;
        }

        if(isset($body['refresh_token'])){
            return self::GRANT_REFRESH_TOKEN;
        }

        throw new auth_failure_exception('Missing grant_type', 400);
    }

    protected function get_user_for_password_grant(ServerRequestInterface $request) : object {
        $body = $request->getParsedBody();

        if(empty($body['username'])){
            throw new auth_failure_exception("Empty username", 400);
        }

        if(empty($body['password'])){
            throw new auth_failure_exception("Empty password", 400);
        }
        
        $user = $this->db->get_record('user', [
            'username' => $body['username'],
            'suspended' => 0,
            'deleted' => 0,
        ]);

        if(!$user || !validate_internal_user_password($user, $body['password'])){
            throw new auth_failure_exception("Invalid user credentials", 401);
        }

        return $user;
    }


    protected function get_user_for_refresh_token_grant(ServerRequestInterface $request) : object {
        $body = $request->getParsedBody();

        try {
            $refresh_token = $this->token_service->parse_refresh_token($body['refresh_token'] ?? '');

            $user = $this->db->get_record('user', [
                'id' => $refresh_token->get_user_id(),
                'suspended' => 0,
                'deleted' => 0,
            ]);

            return $user;

        } catch (\Throwable $th) {
            throw new auth_failure_exception('Invalid refresh token', 401);
        }
    }

    protected function get_user_for_client_credentials_grant(ServerRequestInterface $request) : object {
        $body = $request->getParsedBody();

        if(empty($body['client_id'])){
            throw new auth_failure_exception("Empty client id", 400);
        }

        if(empty($body['client_secret'])){
            throw new auth_failure_exception("Empty client secret", 400);
        }

        $service = new oauth_credentials_service();
        $credentials = $service->validate_credentials($body['client_id'], $body['client_secret']);

        if(!$user = $credentials->get_user()){
            throw new auth_failure_exception('Invalid client user', 401);
        }

        return $user;
    }

    protected function validate_user(?object $user = null){
        global $CFG;

        if(empty($user) || (bool) $user->deleted){
            throw new auth_failure_exception("User not found", 401);
        }

        if(!$user->confirmed){
            throw new auth_failure_exception("User not confirmed", 401);
        }

        $has_policy = $CFG->sitepolicy || $CFG->sitepolicyguest;
        if($has_policy && !$user->policyagreed){
            throw new auth_failure_exception("Policy not agreed", 401);
        }
    }

    protected function generate_access_token_for_user(object $user) : object {
        return (object) [
            "access_token" => $this->token_service->generate_access_token($user)->get_token(),
            "refresh_token" => $this->token_service->generate_refresh_token($user)->get_token(),
            "token_type" => "Bearer",
            "expires_in" => config::instance()->get_jwt_access_token_ttl(),
        ];
    }
    
    /**
     * POST /oauth/token
     *
     * @param ServerRequestInterface $request
     * @return object
     */
    #[OA\Post(
        path: "/oauth/token",
        summary: "Generate an access token",
        description: "Handles OAuth authentication and returns an access token.",
        tags: ["OAuth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                oneOf: [
                    new OA\Schema(
                        description: "Password grant request",
                        required: ["grant_type", "username", "password"],
                        properties: [
                            new OA\Property(property: "grant_type", type: "string", example: "password"),
                            new OA\Property(property: "username", type: "string", example: "user@example.com"),
                            new OA\Property(property: "password", type: "string", example: "securepassword")
                        ]
                    ),
                    new OA\Schema(
                        description: "Client credentials grant request",
                        required: ["grant_type", "client_id", "client_secret"],
                        properties: [
                            new OA\Property(property: "grant_type", type: "string", example: "client_credentials"),
                            new OA\Property(property: "client_id", type: "string", example: "your-client-id"),
                            new OA\Property(property: "client_secret", type: "string", example: "your-client-secret")
                        ]
                    ),
                    new OA\Schema(
                        description: "Refresh token grant request",
                        required: ["grant_type", "refresh_token"],
                        properties: [
                            new OA\Property(property: "grant_type", type: "string", example: "refresh_token"),
                            new OA\Property(property: "refresh_token", type: "string", example: "existing-refresh-token")
                        ]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Access token generated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "access_token", type: "string", example: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
                        new OA\Property(property: "refresh_token", type: "string", example: "dXNlcm5hbWU6ZXhhbXBsZS5jb20K..."),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid request",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Missing grant_type")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid credentials",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid user credentials")
                    ]
                )
            )
        ]
    )]
    public function issue_token(ServerRequestInterface $request) : object {
        $user = match ($this->detect_grant_type($request)) {
            self::GRANT_PASSWORD => $this->get_user_for_password_grant($request),
            self::GRANT_REFRESH_TOKEN => $this->get_user_for_refresh_token_grant($request),
            self::GRANT_CLIENT_CREDENTIALS => $this->get_user_for_client_credentials_grant($request),
            default => throw new auth_failure_exception('Invalid grant_type', 400),
        };
        
        $this->validate_user($user);
        return $this->generate_access_token_for_user($user);
    }
}
