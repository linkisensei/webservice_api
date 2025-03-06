<?php

$router->get('/mock1', function ($request){
    return ['mock1' => true];
})->setName('mocked.route1');

$router->get('/mock2', function ($request){
    return ['mock2' => true];
})->setName('mocked.route2');

$router->get('/mock3', function ($request){
    return ['mock3' => true];
})->setName('mocked.route3');