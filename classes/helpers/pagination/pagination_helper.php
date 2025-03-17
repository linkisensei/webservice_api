<?php namespace webservice_api\helpers\pagination;

use \Psr\Http\Message\ServerRequestInterface;
use \moodle_url;

class pagination_helper {
    protected string $page_param = 'page';
    protected string $limit_param = 'limit';
    protected int $limit = 20;
    protected int $min_page = 1;

    protected string $uri;
    protected string $method;
    protected array $query_params = [];

    public function __construct(ServerRequestInterface $request) {
        $this->uri = $request->getUri();
        $this->method = $request->getMethod();
        $this->query_params = $request->getQueryParams();
    }

    public function get_page(): int {
        return max((int)($this->query_params[$this->page_param] ?? $this->min_page), $this->min_page);
    }

    public function get_limit(): int {
        return (int)($this->query_params[$this->limit_param] ?? $this->limit);
    }

    public function set_limit(int $limit): static {
        $this->limit = $limit;
        return $this;
    }

    public function get_current_page_url(): moodle_url {
        $params = $this->query_params;
        $params[$this->page_param] = $this->get_page();
        $params[$this->limit_param] = $this->get_limit();

        return new moodle_url($this->uri, $params);
    }

    public function make_next_page_url(): moodle_url {
        $params = $this->query_params;
        $params[$this->page_param] = $this->get_page() + 1;
        $params[$this->limit_param] = $this->get_limit();

        return new moodle_url($this->uri, $params);
    }

    public function make_previous_page_url(): ?moodle_url {
        $current_page = $this->get_page();
        if ($current_page <= $this->min_page) {
            return null;
        }

        $params = $this->query_params;
        $params[$this->page_param] = $current_page - 1;
        $params[$this->limit_param] = $this->get_limit();

        return new moodle_url($this->uri, $params);
    }


    public function define_limit_param(string $limit_param): static {
        $this->limit_param = $limit_param;
        return $this;
    }

    public function define_page_param(string $page_param): static {
        $this->page_param = $page_param;
        return $this;
    }

    public function define_min_page(int $min_page): static {
        $this->min_page = $min_page;
        return $this;
    }
}