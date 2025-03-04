<?php namespace local_api\event\api_auth_failed;

class api_auth_failed extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "API authentication failed: \"{$this->other['reason']}\".";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event:api_auth_failed', 'local_api');
    }

    /**
     * Custom validation.
     *
     * It is recommended to set the properties:
     * - $other['username']
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['reason'])) {
           throw new \coding_exception('The \'reason\' value must be set in other.');
        } else if (!isset($this->other['method'])) {
           throw new \coding_exception('The \'method\' value must be set in other.');
        } else if (isset($this->other['token'])) {
           throw new \coding_exception('The \'token\' value must not be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
