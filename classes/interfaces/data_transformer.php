<?php namespace webservice_api\interfaces;

interface data_transformer {

    /**
     * Transforms the given data into the desired format.
     *
     * @param mixed $data Raw data
     * @return array Transformed data.
     */
    public function transform(mixed $data): array;

    /**
     * Allows the transformer to be used as a callable.
     *
     * @param mixed $data Raw data
     * @return array Transformed data.
     */
    public function __invoke(mixed $data): array;
}
