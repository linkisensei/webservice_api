<?php namespace webservice_api\http\response\transformers;

abstract class abstract_defined_transformer extends abstract_data_transformer {

    /**
     * Defines what properties must returned by the transformer.
     * It also defines its type.
     * 
     * Example:
     * 
     * [
     *     'id' => PARAM_INT,
     *     'name' => PARAM_TEXT,
     *     'preferences' => self::PARAM_RAW,
     *      ...
     * ]
     *
     * @return array
     */
    abstract protected function define_properties() : array;

    public function transform(mixed $data): array {
        if(!is_object($data) && !is_array($data)){
            throw new \InvalidArgumentException("Expected an array or object");
        }

        $transformed_data = [];

        $definiton = $this->define_properties();

        foreach ((array) $data as $key => $value) {
            if(isset($definiton[$key])){
                $transformed_data[$key] = $this->clean_param($value, $definiton[$key]);
            }
        }

        return $transformed_data;
    }

    protected function clean_param(mixed $value, string $type) : mixed {
        if($type == PARAM_RAW){
            return $value;
        }

        if($type == PARAM_BOOL){
            return (bool) clean_param($value, $type);
        }

        return clean_param($value, $type);
    }
}
