<?php namespace webservice_api;

require_once(__DIR__ . '/../vendor/autoload.php');

use \advanced_testcase;
use \webservice_api\config;
use webservice_api\services\jwt_token_service;

class jwt_token_service_test extends advanced_testcase {

    protected $token_service;

    protected function setUp(): void {
        parent::setUp();
        config::generate_secrets();
        $this->token_service = new jwt_token_service();
    }

    public function test_generate_and_parse_access_token() {
        $this->resetAfterTest();
        $user = (object) ['id' => 1];

        $access_token = $this->token_service->generate_access_token($user);
        $this->assertNotEmpty($access_token->get_token());

        $access_parsed = $this->token_service->parse_access_token($access_token->get_token());
        $this->assertEquals(1, $access_parsed->get_user_id());

        $refresh_token = $this->token_service->generate_access_token($user);
        $this->assertNotEmpty($refresh_token->get_token());

        $refresh_parsed = $this->token_service->parse_access_token($refresh_token->get_token());
        $this->assertEquals(1, $refresh_parsed->get_user_id());
    }
}
