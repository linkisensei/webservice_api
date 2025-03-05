<?php namespace webservice_api;

use \advanced_testcase;
use \webservice_api\config;
use \webservice_api\controllers\auth\auth_controller;
use \webservice_api\exceptions\auth_failure_exception;
use \Laminas\Diactoros\ServerRequest;

class auth_controller_test extends advanced_testcase {

    protected $auth_controller;

    protected function setUp(): void {
        parent::setUp();
        config::generate_secrets();
        $this->auth_controller = new auth_controller();
    }

    public function test_create_token_with_invalid_credentials() {
        $this->resetAfterTest();

        $request = new ServerRequest([], [], null, null, 'php://input', [], [], [], json_encode([
            'username' => 'invalid',
            'password' => 'wrongpassword'
        ]));

        $this->expectException(auth_failure_exception::class);
        $this->auth_controller->create_token($request);
    }

    public function test_create_token_with_valid_credentials() {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user([
            'username' => 'testuser01',
            'password' => 'Senha@123',
            'confirmed' => 1,
        ]);

        $request = new ServerRequest([], [], null, null, 'php://input', [], [], [], json_encode([
            'username' => 'testuser01',
            'password' => 'Senha@123'
        ]));

        $response = $this->auth_controller->create_token($request);
        $response = json_decode($response, true);

        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
    }
}
