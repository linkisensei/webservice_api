<?php namespace webservice_api\helpers\pagination;

use \moodle_url;

/**
 * A forward cursor pagination helper.
 * 
 * Uses the param "_after" by default.
 */
class cursor_pagination_helper extends abstract_pagination_helper {
    protected string $cursor_param = '_after';

    /**
     * Get the cursor value.
     */
    public function get_cursor(): mixed {
        return $this->query_params[$this->cursor_param] ?? null;
    }

    /**
     * Get the current page URL.
     */
    public function get_current_page_url(): moodle_url {
        return $this->new_url($this->uri, array_merge($this->query_params, [$this->limit_param => $this->get_limit()]));
    }

    /**
     * Generate the next page URL with a cursor.
     */
    public function make_next_page_url(mixed $after): moodle_url {
        return $this->new_url($this->uri, array_merge($this->query_params, [
            $this->limit_param => $this->get_limit(),
            $this->cursor_param => $after,
        ]));
    }

    /**
     * Override the cursor parameter name.
     */
    public function override_cursor_param(string $param): static {
        $this->cursor_param = $param;
        return $this;
    }
}
