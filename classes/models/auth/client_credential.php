<?php namespace webservice_api\models\auth;

use \core\persistent;

defined('MOODLE_INTERNAL') || die;

class client_credential extends persistent {
    
    const TABLE = 'webservice_api_credentials';

    protected static function define_properties() {
        return [
            'clientid' => [
                'type' => PARAM_RAW,
            ],
            'clientsecret' => [
                'type' => PARAM_RAW,
            ],
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
            'enabled' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'validuntil' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    protected function before_validate() {
        if(empty($this->get('clientid'))){
            $this->raw_set('clientid', self::generate_client_id($this->get('userid')));
        }

        if(empty($this->get('clientsecret'))){
            $this->generate_client_secret();
        }
    }

    public static function generate_client_id(int $userid) : string {
        return hash('sha256', "user-cred-$userid");
    }

    public function generate_client_secret() : static {
        $hash = hash('sha256', uniqid($this->get('clientid')).bin2hex(random_bytes(8)));
        $this->raw_set('clientsecret', $hash);
        return $this;
    }

    public function get_client_id() : string {
        return $this->get('clientid');
    }

    public function get_client_secret() : string {
        return $this->get('clientsecret');
    }

    public function is_expired() : bool {
        $expiration = (int) $this->get('validuntil') ?: INF;
        return $expiration <= time();
    }

    public function get_user() : ?object {
        global $DB;
        return $DB->get_record('user', ['id' => $this->get('userid')]) ?: null;
    }

    /**
     * Issues a new credential for a user
     *
     * @param integer $userid
     * @param string $name if empty, it will be generated
     * @param int $validuntil
     * @return static
     */
    public static function new(int $userid, string $name = '', int $validuntil = 0) : static {
        $instance = new static(0, (object)[
            'userid' => $userid,
            'name' => $name ?: uniqid(date('Y-m-d-')),
            'enabled' => true,
            'validuntil' => $validuntil,
        ]);

        $instance->save();
        return $instance;
    }

    /**
     * Searches for a client_credential by its
     * client id and secret.
     *
     * @param string $client_id
     * @param string $client_secret
     * @return static|null
     */
    public static function find(string $client_id, string $client_secret) : ?static {
        return self::get_record(['clientid' => $client_id, 'clientsecret' => $client_secret]) ?: null;
    }
}