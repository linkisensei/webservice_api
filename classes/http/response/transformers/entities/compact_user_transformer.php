<?php namespace webservice_api\http\response\transformers\entities;

use \webservice_api\http\response\transformers\abstract_defined_transformer;

class compact_user_transformer extends abstract_defined_transformer {

    protected function define_properties() : array {
        return [
            'id' => PARAM_INT,
            'username' => PARAM_RAW,
            'idnumber' => PARAM_RAW,
            'firstname' => PARAM_RAW,
            'middlename' => PARAM_RAW,
            'lastname' => PARAM_RAW,
        ];
    }
}
