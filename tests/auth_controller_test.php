<?php namespace webservice_api;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/fixtures/traits/mock_request_trait.php');

use \advanced_testcase;
use \webservice_api\config;
use \webservice_api\controllers\auth\auth_controller;
use \webservice_api\exceptions\auth_failure_exception;
use \webservice_api\fixtures\traits\mock_request_trait;

class auth_controller_test extends advanced_testcase {

    use mock_request_trait;

    protected $auth_controller;

    protected function setUp(): void {
        parent::setUp();
        config::generate_secrets();
        $this->auth_controller = new auth_controller();
    }

    public function test_create_token_with_invalid_credentials() {
        $this->resetAfterTest();

        $request = $this->make_request('POST', '/api/token', [
            'username' => 'invalid',
            'password' => 'wrongpassword'
        ]);

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
        
        $request = $this->make_request('POST', '/api/token', [
            'username' => 'testuser01',
            'password' => 'Senha@123'
        ]);
    
        $response = (array) $this->auth_controller->create_token($request);
        
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
    }
}
