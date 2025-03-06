<?php namespace webservice_api;

use \advanced_testcase;
use \webservice_api\config;
use \League\Route\Router;
use \webservice_api\routing\route_manager;

class route_manager_test extends advanced_testcase {

    /**
     * @group current
     *
     * @return void
     */
    public static function test_plugin_routes(){
        $router = new Router();

        route_manager::register(function($router){
            $router->get('/mock', function ($request){
                return ['mock' => true];
            })->setName('mocked.route');
        });

        route_manager::apply_routes($router);

        $router->getNamedRoute('mocked.route');
    }

    /**
     * @group current
     *
     * @return void
     */
    public static function test_plugin_routes_file(){
        global $CFG;

        $router = new Router();

        route_manager::register_file($CFG->dirroot . '/webservice/api/tests/fixtures/test_routes.php');
        route_manager::apply_routes($router);

        $router->getNamedRoute('mocked.route1');
        $router->getNamedRoute('mocked.route2');
        $router->getNamedRoute('mocked.route3');
    }
}
