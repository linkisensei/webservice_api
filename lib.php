<?php

function webservice_api_after_config(){
    \webservice_api\routing\route_manager::register(function($router){
        $router->get('/test', function ($request){
            return ['test' => 'test2'];
        });
    });
}