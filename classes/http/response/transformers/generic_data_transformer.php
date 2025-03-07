<?php namespace webservice_api\http\response\transformers;

use \Closure;

class generic_data_transformer extends abstract_data_transformer {

    protected Closure $callback;

    /**
     * @param Closure $callback function(mixed $data): array;
     */
    public function __construct(Closure $callback){
        $this->callback = $callback;
    }

    public function transform(mixed $data): array {
        return ($this->callback)($data);
    }
}
