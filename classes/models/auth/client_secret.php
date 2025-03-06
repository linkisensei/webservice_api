<?php namespace webservice_api\models\auth;

use \core\persistent;
use \webservice_api\models\auth\client;

defined('MOODLE_INTERNAL') || die;

class client_secret extends persistent {
    
    const TABLE = 'webservice_api_clientsecrets';

    protected client $client;
    protected ?string $secret = null;

    protected static function define_properties() {
        return [
            'client' => [
                'type' => PARAM_INT,
            ],
            'secretid' => [
                'type' => PARAM_RAW,
            ],
            'secrethash' => [
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
        if(empty($this->secret)){
            $this->generate_client_secret();
        }
    }


    public function generate_client_secret() : static {
        $this->secret = uniqid().bin2hex(random_bytes(16));
        $this->raw_set('secrethash', static::hash_secret($this->secret));
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
    public function get_secret() : ?string {
        return $this->secret;
    }

    public static function get_by_secretid(string $secretid) : ?static {
        return static::get_record(['secretid' => $secretid]) ?: null;
    }

    public static function list_by_client(client $client) : array {
        return static::get_records(['client' => $client->get('id')]);
    }

    /**
     * Searches for a client_secret by its
     * client id and secret.
     *
     * @param string|int $clientid
     * @param string $secret
     * @return static|null
     */
    public static function find(string|int $clientid, string $secret) : ?static {
        global $DB;

        $clients_table = '{'.client::TABLE.'}';
        $secrets_table = '{'.static::TABLE.'}';

        $params = [
            'clientid' => $clientid,
            'secrethash' => self::hash_secret($secret),
        ];

        if(is_numeric($clientid)){
            $sql = "SELECT cs.*
                FROM $secrets_table cs
                WHERE cs.client = :clientid
                    AND cs.secret = :secrethash";
        }else{
            $sql = "SELECT cs.*
                FROM $clients_table c
                    JOIN $secrets_table cs
                WHERE c.clientid = :clientid
                    AND cs.secret = :secrethash";
        }

        if($record = $DB->get_record_sql($sql, $params)){
            return new static(0, $record);
        }
        return null;
    }

    public static function create_from_client(client $client, array|object $data = []){
        $data = (object) $data;
        $data->client = $client->get('id');
        return (new static(0, $data))->set_client($client);
    }
}