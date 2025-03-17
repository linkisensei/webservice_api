<?php namespace webservice_api\controllers;

use \moodle_url;
use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\exceptions\api_exception;
use \moodle_database;
use \webservice_api\traits\request_params_trait;

abstract class abstract_controller {
    use request_params_trait;

    protected moodle_database $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }    
}
