<?php namespace webservice_api\factories\resources;

use \webservice_api\http\response\resources\api_resource;
use \webservice_api\http\response\resources\hal_resource;
use \webservice_api\factories\resources\api_resource_factory;

class hal_resource_factory extends api_resource_factory {

    protected static function make_resource(array|object $attributes = []) : api_resource {
        return new hal_resource($attributes);
    }
}
