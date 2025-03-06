<?php namespace webservice_api\services;

use \context;
use \webservice_api\config;
use \webservice_api\models\auth\client;
use \webservice_api\models\auth\client_secret;
use \webservice_api\exceptions\api_exception;

final class client_credentials_service {

    private config $config;
    private context $context;

    public function __construct(){
        $this->config = config::instance();
        $this->context = \context_system::instance();
    }

    public function create_client(int $userid){
        $this->check_permissions($userid);

        $client = new client(0, (object)['userid' => $userid]);
        $client->save();

        return $client;
    }

    public function get_client(int $userid){
        if($client = client::get_by_user_id($userid)){
            return $client;
        }

        throw new api_exception('Client not found!', 404);
    }

    public function create_client_secret(string|client $client, string $name = '', int $valid_until = 0) : client_secret {
        if(is_string($client)){
            $client = client::get_by_client_id($client);
        }

        if(empty($client)){
            throw new api_exception('Client not found!', 404);
        }

        $this->check_permissions((int) $client->get('userid'));

        $secret = new client_secret(0, (object) [
            'name' => $name,
            'validuntil' => $valid_until,
        ]);

        $secret->set_client($client)->save();
        return $secret;
    }

    public function update_client_secret(string|client $client, string $name = '', int $valid_until = 0) : client_secret {
        if(is_string($client)){
            $client = client::get_by_client_id($client);
        }

        if(empty($client)){
            throw new api_exception('Client not found!', 404);
        }

        $this->check_permissions((int) $client->get('userid'));

        $secret = new client_secret(0, (object) [
            'name' => $name,
            'validuntil' => $valid_until,
        ]);

        $secret->set_client($client)->save();
        return $secret;
    }

    public function validate_credentials(string $client_id, string $client_secret) : client_secret {
        return client_secret::find($client_id, $client_secret);
    }

    /**
     * @throws \required_capability_exception
     * @param integer $userid
     * @return void
     */
    protected function check_permissions(int $userid){
        global $USER;

        if($USER->id != $userid){
            require_capability('webservice/api:managecredentials', $this->context);
        }else{
            require_capability('webservice/api:managecredentials', $this->context);
        }
    }
}
