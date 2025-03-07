<?php namespace webservice_api\routing\traits;

use \moodle_url;

/**
 * API Route Helper Trait
 *
 * Provides utility methods for generating and manipulating API route URIs.
 */
trait api_route_helper {

    /**
     * Generates an absolute API URI from a relative path.
     *
     * @param string $relative The relative path (e.g., 'users/1').
     * @return string The absolute API URI.
     */
    public static function get_api_absolute_uri(string $relative = ''): string {
        $relative = trim($relative, '/');
        $url = new moodle_url("/webservice/api/{$relative}");
        return $url->out(false);
    }

    /**
     * Extracts the relative API URI from an absolute URL.
     *
     * @param string $absolute The absolute URL (e.g., 'https://example.com/webservice/api/users/1').
     * @return string The relative API URI (e.g., '/users/1').
     */
    public static function get_api_relative_uri(string $absolute): string {
        $root = new moodle_url('/webservice/api');
        return '/' . ltrim(substr($absolute, strlen($root->get_path())), '/');
    }
}
