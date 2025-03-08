<?php namespace webservice_api\event;

use \webservice_api\models\auth\oauth_credentials;

/**
 * Event oauth_credentials_deleted
 *
 * @package webservice_api
 */
class oauth_credentials_deleted extends \core\event\base {

    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'webservice_api_credentials';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('event:oauth_credentials_deleted', 'webservice_api');
    }

    public function get_description() {
        return "OAuth credentials were updated for user {$this->relateduserid}.";
    }

    public static function from_oauth_credentials(oauth_credentials $credentials) : static {
        $event = self::create([
            'context' => \context_system::instance(),
            'objectid' => $credentials->get('id'),
            'relateduserid' => $credentials->get('userid'),
        ]);
        $event->add_record_snapshot('webservice_api_credentials', $credentials->to_record());
        return $event;
    }
}
