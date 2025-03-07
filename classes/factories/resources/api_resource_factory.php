<?php namespace webservice_api\factories\resources;

use \moodle_url;
use \webservice_api\http\response\resources\api_resource;
use \webservice_api\helpers\routing\api_route_helper;
use \webservice_api\models\auth\oauth_credentials;

class api_resource_factory {
    protected static function make_resource(array|object $attributes = []) : api_resource {
        return new api_resource($attributes);
    }

    public static function make_user_resource(array|object $user) : api_resource {
        $user = (array) $user;
        $user['id'] = (int) $user['id'];
        $resource = static::make_resource($user);
        return $resource;
    }

    public static function make_oauth_credentials_resource(oauth_credentials $secret) : api_resource {
        $record = $secret->to_record();

        unset($record->id);
        unset($record->user_id);
        unset($record->secret_hash);
        unset($record->modified_by);

        unset($record->timecreated); // Colateral effect of persistent
        unset($record->timemodified); // Colateral effect of persistent
        unset($record->usermodified); // Colateral effect of persistent

        if($secret_raw = $secret->get_secret()){
            $record->client_secret = $secret_raw;
        }

        $resource = static::make_resource($record);

        // Adding links
        $uri = api_route_helper::get_api_absolute_uri("/credentials/$record->client_id");

        $resource->add_link('self', $uri, 'GET');
        $resource->add_link('regenerate', $uri, 'PATCH');
        $resource->add_link('revoke', $uri, 'DELETE');

        return $resource;
    }

    public static function __callStatic(string $name, array $args): api_resource {
        return static::make_resource($args[0] ?? []);
    }
}
