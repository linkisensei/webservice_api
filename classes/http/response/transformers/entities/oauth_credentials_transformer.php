<?php namespace webservice_api\http\response\transformers\entities;

use \webservice_api\http\response\transformers\abstract_data_transformer;
use \webservice_api\models\auth\oauth_credentials;

class oauth_credentials_transformer extends abstract_data_transformer {

    public function transform(mixed $data): array {
        if(!($data instanceof oauth_credentials)){
            throw new \InvalidArgumentException("Expected instance of \"webservice_api\models\auth\oauth_credentials\"");
        }

        return [
            'client_id' => $data->get('client_id'),
            'client_secret' => $data->get_secret(),
            'expires_at' => intval($data->get('expires_at')),
            'created_at' => intval($data->get('created_at')),
            'modified_at' => intval($data->get('modified_at')),
        ];
    }
}
