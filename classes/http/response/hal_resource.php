<?php namespace local_api\http\response;

use \local_api\http\response\api_resource;

/**
 * Class hal_resource
 *
 * This class implements the HAL (Hypertext Application Language) format, 
 * a standard for structuring REST API responses. HAL allows the inclusion 
 * of navigational links and embedded resources, making APIs more 
 * discoverable and self-descriptive.
 *
 * More information about HAL:
 * @see https://tools.ietf.org/html/draft-kelly-json-hal-08
 */
class hal_resource extends api_resource {

    protected string $format = 'HAL';

    public function jsonSerialize(): mixed {
        $response = $this->attributes;

        if (!empty($this->links)) {
            $response['_links'] = $this->links;
        }

        if (!empty($this->embedded)) {
            $response['_embedded'] = $this->embedded;
        }

        return $response;
    }
}