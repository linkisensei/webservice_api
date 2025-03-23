<?php namespace webservice_api\helpers\routing;

use \moodle_url;

/**
 * API Route Helper
 *
 * Provides utility methods for generating and manipulating API route URIs.
 */
class api_route_helper {

    /**
     * Generates an absolute API URI from a relative path.
     *
     * @param string $relative The relative path (e.g., 'users/1').
     * @param array $params
     * @return string The absolute API URI.
     */
    public static function get_api_absolute_uri(string $relative = '', array $params = []): string {
        $relative = trim($relative, '/');
        $url = new moodle_url("/webservice/api/{$relative}", $params);
        return $url->out(false);
    }

    /**
     * Extracts the relative API URI from an absolute URL.
     *
     * @param string $absolute The absolute URL (e.g., 'https://example.com/webservice/api/users/1').
     * @param array $params
     * @return string The relative API URI (e.g., '/users/1').
     */
    public static function get_api_relative_uri(string $absolute, array $params = []): string {
        $root = new moodle_url('/webservice/api', $params);
        return '/' . ltrim(substr($absolute, strlen($root->get_path())), '/');
    }

    /**
     * Returns the root URI of the API
     *
     * @return string API root (e.g., 'https://example.com/webservice/api')
     */
    public static function get_api_root_uri() : string {
        return (new moodle_url('/webservice/api'))->out(false);
    }
}