<?php namespace webservice_api\http\response\transformers;

use webservice_api\interfaces\data_transformer;

abstract class abstract_data_transformer implements data_transformer {

    /**
     * Transforms the given data into the desired format.
     *
     * @param mixed $data Raw data
     * @return array Transformed data.
     */
    abstract public function transform(mixed $data): array;

    /**
     * Allows the transformer to be used as a callable.
     *
     * @param mixed $data Raw data
     * @return array Transformed data.
     */
    public function __invoke(mixed $data): array {
        return $this->transform($data);
    }
}
