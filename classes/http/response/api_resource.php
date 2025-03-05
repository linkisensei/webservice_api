<?php namespace webservice_api\http\response;

use \JsonSerializable;

abstract class api_resource implements JsonSerializable {
    protected array $attributes = [];
    protected array $links = [];
    protected array $embedded = [];
    protected string $format = '';

    public function __construct(array|object $attributes = []){
        $this->attributes = (array) $attributes;
    }

    public function add_attribute(string $key, mixed $value): self {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function add_link(string $rel, string $href, array $attributes = []): self {
        $this->links[$rel] = array_merge(['href' => $href], $attributes);
        return $this;
    }

    public function embed(string $key, object|array $resource): self {
        if(is_object($resource) && is_a($resource, JsonSerializable::class, true)){
            $this->embedded[$key] = $resource->jsonSerialize();
        }else{
            $this->embedded[$key] = $resource;
        }
        
        return $this;
    }

    abstract function jsonSerialize(): mixed;
}