<?php namespace webservice_api\controllers;

use \moodle_database;
use \webservice_api\factories\resources\hal_resource_factory;
use \webservice_api\http\response\resources\hal_resource;
use \webservice_api\controllers\abstract_controller;
use \webservice_api\http\response\transformers\entities\compact_user_transformer;

class user_controller extends abstract_controller {
    
    public function get_current_user() : hal_resource {
        global $USER;

        $user_transformer = new compact_user_transformer();
        return hal_resource_factory::make_user_resource($user_transformer($USER));
    }
}
