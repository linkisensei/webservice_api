<?php namespace webservice_api;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/fixtures/mock_external_api.php');
require_once(__DIR__ . '/fixtures/traits/mock_request_trait.php');

use \advanced_testcase;
use \webservice_api\routing\adapters\external_api_adapter;
use \webservice_api\fixtures\traits\mock_request_trait;
class external_api_adapter_test extends advanced_testcase {

    use mock_request_trait;

    /**
     * @runInSeparateProcess
     */
    public function test_adapter_calls_external_api_with_complex_structure() {
        $this->resetAfterTest(true);

        $request = $this->make_request('POST', '/api/token', [
            'user' => [
                'id' => 42,
                'username' => 'testuser'
            ],
            'courses' => [
                ['id' => 1, 'fullname' => 'Sword'],
                ['id' => 2, 'fullname' => 'Shield'],
            ]
        ]);

        
        $adapter = new external_api_adapter(\webservice_api\fixtures\mock_external_api::class, 'mock_method');
        $uri_params = ['id' => 123];
        $response = (array) $adapter($request, $uri_params);

        $this->assertEquals($uri_params['id'], $response['id']);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(42, $response['user']['id']);
        $this->assertEquals('test@email.com', $response['user']['email'], 'Default param missing');
        $this->assertEquals('testuser', $response['user']['username']);
        $this->assertCount(2, $response['courses']);
        $this->assertEquals(1, $response['courses'][0]['id']);
        $this->assertEquals('Sword', $response['courses'][0]['fullname']);
    }
}
