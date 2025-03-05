<?php namespace local_api\controllers;

use \moodle_database;
use \moodle_url;
use \local_api\http\response\hal_resource;
use \local_api\controllers\abstract_controller;

class user_controller extends abstract_controller {
    const RESOURCE_PATH = 'users';

    protected moodle_database $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    public function get_current_user() : hal_resource {
        global $USER;
        $response = new hal_resource($USER);
        $response->add_link('self', $this->get_resource_uri($USER->id));
        return $response;
    }
}
