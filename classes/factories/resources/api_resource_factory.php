<?php namespace webservice_api\factories\resources;

use \moodle_url;
use \webservice_api\http\response\resources\api_resource;
use \webservice_api\helpers\routing\api_route_helper;
use \webservice_api\models\auth\client;
use \webservice_api\models\auth\client_secret;

class api_resource_factory {
    

    protected static function make_resource(array|object $attributes = []) : api_resource {
        return new api_resource($attributes);
    }

    public static function make_user_resource(array|object $user) : api_resource {
        $resource = static::make_resource($user);
        return $resource;
    }

    public static function make_oauth_client_resource(client $client) : api_resource {
        $resource = static::make_resource([
            'id' => $client->get('clientid'),
            'timecreated' => $client->get('timecreated'),
            'timemodified' => $client->get('timemodified'),
        ]);

        // Adding links
        $clientid = $resource->get_attribute('clientid');
        $uri = api_route_helper::get_api_absolute_uri("/clients/$clientid");

        $resource->add_link('self', $uri, 'GET');
        $resource->add_link('delete', $uri, 'DELETE');

        return $resource;
    }

    public static function make_oauth_client_secret_resource(client_secret $secret) : api_resource {
        $resource = static::make_resource([
            'id' => $secret->get('secretid'),
            'name' => $secret->get('name'),
            'validuntil' => $secret->get('validuntil'),
            'timecreated' => $secret->get('timecreated'),
            'timemodified' => $secret->get('timemodified'),
        ]);

        // Client
        $client = $secret->get_client_instance();
        $resource->add_attribute('clientid', $client->get('id'));

        // Adding links
        $secretid = $resource->get_attribute('secretid');
        $clientid = $client->get('clientid');
        $uri = api_route_helper::get_api_absolute_uri("/clients/$clientid/secrets/$secretid");

        $resource->add_link('self', $uri, 'GET');
        $resource->add_link('delete', $uri, 'DELETE');

        return $resource;
    }

    public static function __callStatic(string $name, array $args): api_resource {
        return static::make_resource($args[0] ?? []);
    }
}
