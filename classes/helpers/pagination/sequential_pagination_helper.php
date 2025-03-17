<?php namespace webservice_api\helpers\pagination;

use \Psr\Http\Message\ServerRequestInterface;
use \moodle_url;

class sequential_pagination_helper {
    protected string $limit_param = 'limit';
    protected string $before_param = 'before';
    protected string $after_param = 'after';
    protected int $limit = 20;

    protected string $uri;
    protected string $method;
    protected array $query_params = [];

    public function __construct(ServerRequestInterface $request) {
        $this->uri = $request->getUri();
        $this->method = $request->getMethod();
        $this->query_params = $request->getQueryParams();
    }
    
    public function get_limit() : int {
        return $this->query_params[$this->limit_param] ?? $this->limit;
    }

    public function set_limit(int $limit): static {
        $this->limit = $limit;
        return $this;
    }

    public function get_before() : mixed {
        return $this->query_params[$this->before_param] ?? null;
    }

    public function get_after() : mixed {
        return $this->query_params[$this->after_param] ?? null;
    }

    public function get_current_page_url() : moodle_url {
        $params = $this->query_params;
        $params[$this->limit_param] = $this->limit;
        return new moodle_url($this->uri, $params);
    }

    public function make_next_page_url(mixed $after) : moodle_url {
        $params = $this->query_params;
        $params[$this->limit_param] = $this->limit;
        $params[$this->after_param] = $after;
        unset($params[$this->before_param]);

        return new moodle_url($this->uri, $params);
    }

    public function make_previous_page_url(mixed $before) : moodle_url {
        $params = $this->query_params;
        $params[$this->limit_param] = $this->limit;
        $params[$this->before_param] = $before;
        unset($params[$this->after_param]);

        return new moodle_url($this->uri, $params);
    }

    public function define_limit_param(string $limit_param): static {
        $this->limit_param = $limit_param;
        return $this;
    }
    
    public function set_before_param(string $before_param): static {
        $this->before_param = $before_param;
        return $this;
    }

    public function set_after_param(string $after_param): static {
        $this->after_param = $after_param;
        return $this;
    }
}
