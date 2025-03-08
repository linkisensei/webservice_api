<?php declare(strict_types=1);

namespace webservice_api\factories\router;

use \League\Route\Router;
use \webservice_api\routing\strategies\json_strategy;
use \League\Route\Strategy\StrategyInterface;
use \webservice_api\routing\route_manager;
use \League\Route\RouteCollectionInterface;

class router_factory {

    /**
     * Returns a router instance with loaded routes and
     * strategy applied.
     *
     * @return \League\Route\Router
     */
    public static function make_router() : Router {
        $router = new Router();
        static::init_router($router);
        return $router;
    }

    /**
     * Applies configurations and defines routes
     *
     * @param Router $router
     * @return void
     */
    public static function init_router(Router $router){
        // Setting strategy
        $router->setStrategy(self::make_strategy());

        // Defining routes
        route_manager::register_local_routes($router);
        route_manager::register_from_function_callbacks();
        route_manager::apply_routes($router);
    }

    protected static function make_strategy() : StrategyInterface {
        return json_strategy::factory();
    }
}
