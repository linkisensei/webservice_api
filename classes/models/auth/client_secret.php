<?php namespace webservice_api\models\auth;

use \core\persistent;
use \webservice_api\models\auth\client;

defined('MOODLE_INTERNAL') || die;

class client_secret extends persistent {
    
    const TABLE = 'webservice_api_clientsecrets';

    protected client $client;
    protected ?string $raw_secret = null;

    protected static function define_properties() {
        return [
            'client' => [
                'type' => PARAM_INT,
            ],
            'secret' => [
                'type' => PARAM_RAW,
            ],
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'validuntil' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    protected function before_validate() {
        if(empty($this->get('secret'))){
            $this->generate_client_secret();
        }
    }

    protected function before_create() {
        $this->raw_set('secret', self::hash_secret('secret'));
    }


    public function generate_client_secret() : static {
        $this->raw_secret = $this->raw_get('secret');
        $hash = uniqid($this->get('client')).bin2hex(random_bytes(16));
        $this->raw_set('secret', $hash);
        return $this;
    }

    public static function hash_secret(string $secret) : string {
        return hash('sha256', $secret);
    }

    public function is_expired() : bool {
        $expiration = (int) $this->get('validuntil') ?: INF;
        return $expiration <= time();
    }

    public function set_client(client $client) : static {
        $this->client = $client;
        return $this;
    }

    public function get_client() : ?client {
        if(isset($this->client)){
            return $this->client;
        }

        return client::get_record(['id' => $this->get('client')]) ?: null;
    }

    /**
     * Only retriavable when the object is created
     * 
     * Only the hash is stored and recovered from 
     * the database.
     *
     * @return string
     */
    public function get_raw_secret() : ?string {
        return $this->raw_secret;
    }

    /**
     * Searches for a client_credential by its
     * client id and secret.
     *
     * @param string|int $client_id
     * @param string $client_secret
     * @return static|null
     */
    public static function find(string|int $client_id, string $client_secret) : ?static {
        global $DB;

        $clients_table = '{'.client::TABLE.'}';
        $secrets_table = '{'.static::TABLE.'}';

        $params = [
            'clientid' => $client_id,
            'secrethash' => self::hash_secret($client_secret),
        ];

        if(is_numeric($client_id)){
            $sql = "SELECT cs.*
                FROM $secrets_table cs
                WHERE cs.client = :clientid
                    AND c.secret = :secrethash";
        }else{
            $sql = "SELECT cs.*
                FROM $clients_table c
                    JOIN $secrets_table cs
                WHERE c.uuid = :clientuuid
                    AND c.secret = :secrethash";
        }

        if($record = $DB->get_record_sql($sql, $params)){
            return new static(0, $record);
        }
        return null;
    }
}