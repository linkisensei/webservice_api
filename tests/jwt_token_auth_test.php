<?php namespace webservice_api;

require_once(__DIR__ . '/../vendor/autoload.php');

use \advanced_testcase;
use \webservice_api\config;
use \Laminas\Diactoros\ServerRequest;
use \Laminas\Diactoros\Response\JsonResponse;
use \webservice_api\http\middlewares\auth\jwt_token_auth;
use \webservice_api\services\jwt_token_service;
use \webservice_api\exceptions\auth_failure_exception;
use \Psr\Http\Server\RequestHandlerInterface;

class jwt_token_auth_test extends advanced_testcase {

    protected jwt_token_auth $middleware;
    protected jwt_token_service $token_service;
    protected object $user;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        config::generate_secrets();
        
        $this->user = $this->getDataGenerator()->create_user([
            'username' => 'testuser',
            'auth' => 'manual',
            'confirmed' => 1,
            'suspended' => 0,
            'deleted' => 0,
            'policyagreed' => 1
        ]);
        
        $this->token_service = new jwt_token_service();
        $this->middleware = new jwt_token_auth();
    }

    public function test_valid_token_authentication() {
        global $USER;

        $token = $this->token_service->generate_access_token($this->user)->get_token();
        $request = (new ServerRequest())->withHeader('Authorization', "Bearer $token");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new JsonResponse(['success' => true]));

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals($this->user->id, $USER->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['success' => true]), (string) $response->getBody());
    }

    public function test_missing_token() {
        $request = new ServerRequest();
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(auth_failure_exception::class);
        $this->middleware->process($request, $handler);
    }
}
