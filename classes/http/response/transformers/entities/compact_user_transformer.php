<?php namespace webservice_api\http\response\transformers\entities;

use webservice_api\http\response\transformers\abstract_data_transformer;

class compact_user_transformer extends abstract_data_transformer {

    /**
     * List of allowed keys to be returned in the response.
     *
     * @var array<string>
     */
    protected array $allowed_keys = [
        'id' => true,
        'username' => true,
        'idnumber' => true,
        'firstname' => true,
        'middlename' => true,
        'lastname' => true,
    ];

    public function allow_key(string $key) : static {
        $this->allowed_keys[$key] = true;
        return $this;
    }

    public function disallow_key(string $key) : static {
        unset($this->allowed_keys[$key]);
        return $this;
    }

    public function transform(mixed $data): array {
        if(!is_object($data) && !is_array($data)){
            throw new \InvalidArgumentException("Expected an array or object");
        }

        $user = is_object($data) ? (array) clone $data : $data;
        return array_intersect_key($user, $this->allowed_keys);
    }
}
