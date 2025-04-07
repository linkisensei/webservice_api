<?php namespace webservice_api\traits;

use \webservice_api\exceptions\api_exception;
use \invalid_parameter_exception;

trait request_params_trait {
    /**
     * Retrieves a required parameter from the provided source array.
     * Throws an exception if the parameter is missing.
     *
     * @param array $source The array containing parameters
     * @param string $key The parameter key
     * @param string $type The expected type of the parameter
     * @return mixed The cleaned parameter value
     * @throws api_exception If the required parameter is missing
     * @throws api_exception If the required parameter is of the wrong type
     */
    protected function required_param(array $source, string $key, string $type): mixed {
        if (!isset($source[$key])) {
            throw api_exception::fromApiString('exception:missing_required_key', $key)->setStatusCode(400);
        }

        try {
            return validate_param($source[$key], $type, NULL_NOT_ALLOWED);
        } catch (invalid_parameter_exception $ex) {
            $ctx = (object)[
                'key' => $key,
                'type' => $this->translate_param_type($type),
            ];
            throw api_exception::fromApiString('exception:invalid_param_type', $ctx)->setStatusCode(400);
        }
    }

    /**
     * Retrieves an optional parameter from the provided source array.
     * Returns the default value if the parameter is missing or empty.
     *
     * @param array $source The array containing parameters
     * @param string $key The parameter key
     * @param string $type The expected type of the parameter
     * @param mixed $default The default value to return if the parameter is missing or empty
     * @return mixed The cleaned parameter value or default
     * @throws api_exception If the required parameter is of the wrong type
     */
    protected function optional_param(array $source, string $key, string $type, $default = null): mixed {
        if (!isset($source[$key])) {
            return $default;
        }

        try {
            return validate_param($source[$key], $type, NULL_ALLOWED);
        } catch (invalid_parameter_exception $ex) {
            $ctx = (object)[
                'key' => $key,
                'type' => $this->translate_param_type($type),
            ];
            throw api_exception::fromApiString('exception:invalid_param_type', $ctx)->setStatusCode(400);
        }
    }

    protected function translate_param_type(string $param) : string {
        return match ($param) {
            PARAM_INT, PARAM_INTEGER => 'int',
            PARAM_FLOAT, PARAM_NUMBER, PARAM_LOCALISEDFLOAT => 'float',
            PARAM_BOOL => 'integer',
            default => 'string',
        };
    }
}
