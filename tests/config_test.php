<?php namespace webservice_api;

use \advanced_testcase;
use \webservice_api\config;

class config_test extends advanced_testcase {

    public function test_instance() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        
        $config = config::instance();
        $this->assertInstanceOf(config::class, $config);
    }

    public function test_set_and_get() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $config = config::instance();

        $config->set('test_key', 'test_value');
        $this->assertEquals('test_value', $config->get('test_key'));
    }

    public function test_generate_secrets() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        config::generate_secrets();

        $this->assertNotEmpty(config::instance()->get_jwt_access_token_secret());
        $this->assertNotEmpty(config::instance()->get_jwt_refresh_token_secret());
    }
}
