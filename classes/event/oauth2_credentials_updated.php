<?php namespace webservice_api\event;

use \webservice_api\models\auth\oauth2_credentials;

/**
 * Event oauth2_credentials_updated
 *
 * @package webservice_api
 */
class oauth2_credentials_updated extends \core\event\base {

    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'webservice_api_credentials';
        $this->data['crud'] = 'w';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('event:oauth2_credentials_updated', 'webservice_api');
    }

    public function get_description() {
        return "OAuth credentials were updated for user {$this->relateduserid}.";
    }

    public static function from_oauth2_credentials(oauth2_credentials $credentials) : static {
        $event = self::create([
            'context' => \context_system::instance(),
            'objectid' => $credentials->get('id'),
            'relateduserid' => $credentials->get('user_id'),
        ]);
        $event->add_record_snapshot('webservice_api_credentials', $credentials->to_record());
        return $event;
    }
}
