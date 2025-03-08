<?php namespace webservice_api\event;

defined('MOODLE_INTERNAL') || die();

use context_system;
use Psr\Http\Message\ServerRequestInterface;
use \webservice_api\helpers\routing\api_route_helper;

class api_request_logged extends \core\event\base {

    protected float $start_time = 0;
    protected float $end_time = 0;

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = context_system::instance();
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description(): string {
        $route = $this->data['other']['route'] ?? 'unknown';
        return "API request logged: user {$this->userid} called '{$route}'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:api_request_logged', 'webservice_api');
    }

    public static function get_other_mapping(): array {
        return [
            'route' => self::NOT_MAPPED,
            'http_status' => self::NOT_MAPPED,
            'duration' => self::NOT_MAPPED,
        ];
    }

    public function mark_request_start(): static {
        $this->start_time = microtime(true);
        return $this;
    }

    public function mark_request_end(): static {
        global $USER;

        $this->end_time = microtime(true);

        $this->data['other']['duration'] = round($this->end_time - $this->start_time, 4);
        $this->data['userid'] = $USER->id ?? 0;
        $this->data['relateduserid'] = 0;

        return $this;
    }

    public function set_http_status(int $status): static {
        $this->data['other']['http_status'] = $status;
        return $this;
    }

    public function set_error_message(string $error): static {
        $this->data['other']['error'] = $error;
        return $this;
    }

    public function set_crud_from_method(string $method): static {
        $this->data['crud'] = match (strtoupper($method)) {
            'GET', 'OPTIONS', 'HEAD' => 'r',
            'DELETE' => 'd',
            default => 'w',
        };
        return $this;
    }

    public static function from_request(ServerRequestInterface $request): static {
        global $USER;

        $event = static::create([
            'userid' => $USER->id ?? 0,
            'other' => [
                'route' => api_route_helper::get_api_absolute_uri($request->getUri()->getPath()),
            ],
        ]);

        $event->set_crud_from_method($request->getMethod());
        $event->mark_request_start();
        return $event;
    }
}