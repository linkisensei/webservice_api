<?php namespace webservice_api\http\response\transformers;

use \webservice_api\interfaces\data_transformer_interface;

class collection_transformer extends abstract_data_transformer {

    protected data_transformer_interface $item_transformer;

    /**
     * @param data_transformer_interface $item_transformer Transformer to apply to each item.
     */
    public function __construct(data_transformer_interface $item_transformer) {
        $this->item_transformer = $item_transformer;
    }

    public function transform(mixed $data): array {
        if (!is_iterable($data)) {
            throw new \InvalidArgumentException("Expected iterable data for collection transformation.");
        }

        $result = [];
        foreach ($data as $item) {
            $result[] = $this->item_transformer->transform($item);
        }

        return $result;
    }
}
