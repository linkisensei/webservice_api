<?php namespace webservice_api\controllers;

use \moodle_url;
use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\exceptions\api_exception;
use \moodle_database;

abstract class abstract_controller {
    protected moodle_database $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }
    
    /**
     * Retrieves a required parameter from the provided source array.
     * Throws an exception if the parameter is missing.
     *
     * @param array $source The array containing parameters
     * @param string $key The parameter key
     * @param string $type The expected type of the parameter
     * @return mixed The cleaned parameter value
     * @throws api_exception If the required parameter is missing
     */
    protected function required_param(array $source, string $key, string $type): mixed {
        if (!isset($source[$key])) {
            throw api_exception::fromApiString('exception:missing_required_key', $key)->setStatusCode(400);
        }

        return clean_param($source[$key], $type);
    }

    /**
     * Retrieves an optional parameter from the provided source array.
     * Returns the default value if the parameter is missing or empty.
     *
     * @param array $source The array containing parameters
     * @param string $key The parameter key
     * @param string $type The expected type of the parameter
     * @param mixed $default The default value to return if the parameter is missing or empty
     * @return mixed The cleaned parameter value or default
     */
    protected function optional_param(array $source, string $key, string $type, $default = null): mixed {
        if (!isset($source[$key])) {
            return $default;
        }

        $cleaned = clean_param($source[$key], $type);

        if (empty($cleaned) && $default !== null) {
            return $default;
        }

        return $cleaned;
    }
}
