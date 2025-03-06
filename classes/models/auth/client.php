<?php namespace webservice_api\models\auth;

use \core\persistent;
use webservice_api\exceptions\api_exception;

defined('MOODLE_INTERNAL') || die;

class client extends persistent {
    
    const TABLE = 'webservice_api_clients';

    protected ?object $user = null;

    protected static function define_properties() {
        return [
            'uuid' => [
                'type' => PARAM_RAW,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    protected function before_create() {
        global $DB;

        if($DB->record_exists(static::TABLE, ['userid' => $this->get('userid')])){
            throw new api_exception('Client already exists for the specified user', 409);
        }
    }

    protected function before_validate() {
        if(empty($this->get('uuid'))){
            $this->raw_set('uuid', self::generate_uuid());
        }
    }

    public static function generate_uuid() : string {
        return str_replace('-', '', \core\uuid::generate());
    }

    public function get_user() : ?object {
        global $DB;

        if(empty($this->user)){
            $this->user = $DB->get_record('user', ['id' => $this->get('userid')]) ?: null;
        }
        return $this->user;
    }

    public static function get_by_client_id(string $client_id) : ?static {
        return static::get_record(['uuid' => $client_id]) ?: null;
    }

    public static function get_by_user_id(string $userid) : ?static {
        return static::get_record(['userid' => $userid]) ?: null;
    }
}