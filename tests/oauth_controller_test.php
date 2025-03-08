<?php namespace webservice_api;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/fixtures/traits/mock_request_trait.php');

use \advanced_testcase;
use \webservice_api\config;
use \webservice_api\controllers\auth\oauth2_controller;
use \webservice_api\exceptions\auth_failure_exception;
use \webservice_api\fixtures\traits\mock_request_trait;
use \webservice_api\services\oauth2_token_service;
use \webservice_api\services\oauth2_credentials_service;


class oauth2_controller_test extends advanced_testcase {

    use mock_request_trait;

    protected $oauth2_controller;

    protected function setUp(): void {
        parent::setUp();
        $this->setAdminUser();
        $this->resetAfterTest();
        config::generate_secrets();
        $this->oauth2_controller = new oauth2_controller();
    }


    public function test_issue_token_with_invalid_password() {
        $request = $this->make_request('POST', '/api/oauth2/token', [
            'username' => 'invalid',
            'password' => 'wrongpassword'
        ]);

        $this->expectException(auth_failure_exception::class);
        $this->oauth2_controller->issue_token($request);
    }

    public function test_issue_token_with_valid_password() {
        $user = $this->getDataGenerator()->create_user([
            'username' => 'testuser01',
            'password' => 'Senha@123',
            'confirmed' => 1,
        ]);
        
        $request = $this->make_request('POST', '/api/oauth2/token', [
            'username' => 'testuser01',
            'password' => 'Senha@123'
        ]);
    
        $response = (array) $this->oauth2_controller->issue_token($request);
        
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
    }


    public function test_issue_token_with_valid_refresh_token() {
        $token_service = new oauth2_token_service();
    
        $user = $this->getDataGenerator()->create_user([
            'username' => 'testuser01',
            'password' => 'Senha@123',
            'confirmed' => 1,
        ]);
        
        $request = $this->make_request('POST', '/api/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token_service->generate_refresh_token($user),
        ]);
    
        $response = (array) $this->oauth2_controller->issue_token($request);
        
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
    }

    /**
     * @group current
     *
     * @return void
     */
    public function test_issue_token_with_valid_client_credentials() {   
        $user = $this->getDataGenerator()->create_user([
            'username' => 'testuser01',
            'password' => 'Senha@123',
            'confirmed' => 1,
        ]);

        $service = new oauth2_credentials_service();
        $credentials = $service->generate_credentials($user->id);
        
        $request = $this->make_request('POST', '/api/oauth2/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $credentials->get('client_id'),
            'client_secret' => $credentials->get_secret(),
        ]);
    
        $response = (array) $this->oauth2_controller->issue_token($request);
        
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertArrayHasKey('expires_in', $response);
    }
}
