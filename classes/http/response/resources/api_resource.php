<?php namespace webservice_api\http\response\resources;

use \JsonSerializable;
use \moodle_url;

abstract class api_resource implements JsonSerializable {
    protected array $attributes = [];
    protected array $links = [];
    protected array $embedded = [];
    protected string $format = '';

    public function __construct(array|object $attributes = []){
        $this->attributes = (array) $attributes;
    }

    public function add_attribute(string $key, mixed $value): static {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function get_attribute(string $key): mixed {
        return $this->attributes[$key] ?? null;
    }

    public function remove_attribute(string $key): static {
        unset($this->attributes[$key]);
        return $this;
    }

    public function add_link(string $rel, string|moodle_url $href, string $method = 'GET', array $attributes = []): static {
        if($href instanceof moodle_url){
            $href = $href->out(false);
        }

        $this->links[$rel] = array_merge(['href' => $href, 'method' => strtoupper($method)], $attributes);
        return $this;
    }

    public function embed(string $key, object|array $resource): static {
        if(is_object($resource) && is_a($resource, JsonSerializable::class, true)){
            $this->embedded[$key] = $resource->jsonSerialize();
        }else{
            $this->embedded[$key] = $resource;
        }
        
        return $this;
    }

    abstract function jsonSerialize(): mixed;

    /**
     * Makes multiple instances of this resource from a
     * iterator containing its attributes
     *
     * @param iterator|array $colletion
     * @return array
     */
    public static function from_collection(\iterator|array $colletion) : array {
        $instances = [];

        foreach ($colletion as $attributes) {
            $instances[] = new static($attributes);
        }

        return $instances;
    }
}