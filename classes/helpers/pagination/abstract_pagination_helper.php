<?php namespace webservice_api\helpers\pagination;

use Psr\Http\Message\ServerRequestInterface;
use \moodle_url;
use \coding_exception;
use \webservice_api\helpers\routing\api_route_helper;

abstract class abstract_pagination_helper {
    protected string $limit_param = '_limit';
    protected int $default_limit;
    protected bool $force_limit = false;
    protected string $uri;
    protected string $method;
    protected array $query_params = [];
    protected ?string $custom_url_class = null;

    public function __construct(ServerRequestInterface $request, int $limit) {
        $this->uri = api_route_helper::get_api_absolute_uri($request->getUri()->getPath());
        $this->method = $request->getMethod();
        $this->query_params = $request->getQueryParams();
        $this->default_limit = $limit;
    }

    /**
     * Get the limit for pagination.
     */
    public function get_limit(): int {
        return $this->force_limit ? $this->default_limit : (int) ($this->query_params[$this->limit_param] ?? $this->default_limit);
    }

    /**
     * Force the default limit.
     */
    public function force_limit(): static {
        $this->force_limit = true;
        return $this;
    }

    /**
     * Override the limit parameter name.
     */
    public function override_limit_param(string $param): static {
        $this->limit_param = $param;
        return $this;
    }

    /**
     * In case you need to extend the moodle_url class
     * 
     * An example of what this is usefull for is if you
     * plan on using LHS Brackets like "/items?price[gte]=10"
     * since moodle_url does not allow for array query params.
     *
     */
    public function override_url_class(string $class): static {
        if (!is_subclass_of($class, moodle_url::class) && $class !== moodle_url::class) {
            throw new coding_exception("$class must be a subclass of moodle_url");
        }
        $this->custom_url_class = $class;
        return $this;
    }

    /**
     * Create a new URL instance.
     */
    protected function new_url(string $uri, ?array $params = null, ?string $anchor = null): moodle_url {
        $class = $this->custom_url_class ?? moodle_url::class;
        return new $class($uri, $params, $anchor);
    }
}
