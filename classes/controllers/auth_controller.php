<?php namespace local_api\controllers;

use \Psr\Http\Message\ServerRequestInterface;
use \moodle_database;

class auth_controller {

    protected moodle_database $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    public static function create_token(ServerRequestInterface $request) : object {
        return (object) ['test' => 'test'];
    }
}
