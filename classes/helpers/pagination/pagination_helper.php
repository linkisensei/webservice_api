<?php namespace webservice_api\helpers\pagination;

use \moodle_url;

class pagination_helper extends abstract_pagination_helper {
    protected string $page_param = '_page';
    protected int $first_page = 1;

    /**
     * Get the current page number.
     */
    public function get_page(): int {
        return max((int) ($this->query_params[$this->page_param] ?? $this->first_page), $this->first_page);
    }

    /**
     * Get the current page URL.
     */
    public function get_current_page_url(): moodle_url {
        return $this->new_url($this->uri, array_merge($this->query_params, [
            $this->page_param => $this->get_page(),
            $this->limit_param => $this->get_limit(),
        ]));
    }

    /**
     * Generate the next page URL.
     */
    public function make_next_page_url(): moodle_url {
        return $this->new_url($this->uri, array_merge($this->query_params, [
            $this->page_param => $this->get_page() + 1,
            $this->limit_param => $this->get_limit(),
        ]));
    }

    /**
     * Generate the previous page URL.
     */
    public function make_previous_page_url(): ?moodle_url {
        $current_page = $this->get_page();
        if ($current_page <= $this->first_page) {
            return null;
        }
        return $this->new_url($this->uri, array_merge($this->query_params, [
            $this->page_param => $current_page - 1,
            $this->limit_param => $this->get_limit(),
        ]));
    }

    /**
     * Override the page parameter name.
     */
    public function override_page_param(string $param): static {
        $this->page_param = $param;
        return $this;
    }

    /**
     * Override the minimum page value.
     */
    public function override_first_page(int $first_page): static {
        $this->first_page = $first_page;
        return $this;
    }
}
