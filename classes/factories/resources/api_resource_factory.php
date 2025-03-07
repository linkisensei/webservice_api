<?php namespace webservice_api\factories\resources;

use \webservice_api\http\response\resources\api_resource;
use \webservice_api\helpers\routing\api_route_helper;
use \webservice_api\models\auth\oauth_credentials;
use \webservice_api\http\response\transformers\entities\oauth_credentials_transformer;
use \webservice_api\http\response\transformers\entities\compact_user_transformer;

class api_resource_factory {
    protected static function make_resource(array|object $attributes = []) : api_resource {
        return new api_resource($attributes);
    }

    public static function make_user_resource(array|object $user) : api_resource {
        $resource = static::make_resource($user);
        return $resource;
    }

    public static function make_oauth_credentials_resource(oauth_credentials $credentials) : api_resource {
        $credentials_transformer = new oauth_credentials_transformer();
        $resource = static::make_resource($credentials_transformer->transform($credentials));

        // Embedding user
        if($user = $credentials->get_user()){
            $user_transformer = new compact_user_transformer();
            $user_resource = static::make_user_resource($user_transformer->transform($credentials->get_user()));
            $resource->embed('user', $user_resource);
        }

        // Adding links
        $uri = api_route_helper::get_api_absolute_uri('/credentials/' . $credentials->get('client_id'));

        // $resource->add_link('self', $uri, 'GET');
        $resource->add_link('regenerate', $uri, 'PATCH');
        $resource->add_link('revoke', $uri, 'DELETE');

        return $resource;
    }

    public static function __callStatic(string $name, array $args): api_resource {
        return static::make_resource($args[0] ?? []);
    }
}
