<?php namespace webservice_api\controllers;

use \moodle_url;

abstract class abstract_controller {
    protected const RESOURCE_PATH = ''; // Should be overriden by children

    /**
     * Returns the full resource URI
     *
     * @param string $relative Additional relative path
     * @return string Full URI
     */
    protected function get_resource_uri(string $relative = ''): string {
        $relative = trim($relative, '/');

        if($path = trim(static::RESOURCE_PATH, '/')){
            $url = new moodle_url("/webservice/api/{$path}/{$relative}");
        }else{
            $url = new moodle_url("/webservice/api/{$relative}");
        }
        
        return $url->out(false);
    }
}
