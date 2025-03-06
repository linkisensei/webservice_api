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

    public function create_client(int $userid) : client {
        $this->check_permissions($userid);

        $client = new client(0, (object)['userid' => $userid]);
        $client->save();

        return $client;
    }

    public function get_client(string $clientid) : client {
        return $this->get_client_and_check_permissions($clientid);
    }

    protected function get_client_and_check_permissions(string|client $client) : client {
        $client = is_string($client) ? client::get_by_clientid($client) : $client;

        if(!$client){
            throw new api_exception('Client not found!', 404);
        }

        $this->check_permissions((int) $client->get('userid'));
        return $client;
    }

    public function delete_client(string|client $client) : client {
        global $DB;
        
        $client = $this->get_client_and_check_permissions($client);

        try {
            $transaction = $DB->start_delegated_transaction();

            foreach ($client->get_secrets() as $secret) {
                $secret->delete();
            }
    
            $client->delete();
            $transaction->allow_commit();

        } catch (\Throwable $th) {
            $transaction->rollback($th);
        }

        return $client;
    }

    public function list_client_secrets(string|client $client) : array {
        $client = $this->get_client_and_check_permissions($client);
        return $client->get_secrets();
    }

    public function create_client_secret(string|client $client, string $name = '', int $valid_until = 0) : client_secret {
        $client = $this->get_client_and_check_permissions($client);

        $secret = client_secret::create_from_client($client, [
            'name' => $name,
            'validuntil' => $valid_until,
        ]);

        $secret->save();
        return $secret;
    }

    public function update_client_secret(string|client $client, string $secretid, string $name, int $valid_until) : client_secret {
        $secret = $this->get_client_secret($client, $secretid);

        $secret->set('name', $name);
        $secret->set('validuntil', $valid_until);

        $secret->save();
        return $secret;
    }

    public function get_client_secret(string|client $client, string $secretid) : client_secret {
        $client = $this->get_client_and_check_permissions($client);
        $secret = client_secret::get_record(['client' => $client->get('id'), 'secretid' => $secretid]);
        
        if(!$secret){
            throw new api_exception('Client secret not found!', 404);
        }

        return $secret;
    }

    public function validate_credentials(string $clientid, string $secret) : ?client_secret {
        return client_secret::find($clientid, $secret);
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
