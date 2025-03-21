<?php namespace webservice_api\exceptions;

use \Exception;
use \invalid_parameter_exception;

class validation_exception extends api_exception {

    const TYPE_MISMATCH_REGEX = '/Invalid external api parameter: the value is "(.*?)", the server was expecting "(.*?)" type/';
    const INVALID_ARRAY_REGEX = '/Only arrays accepted\. The bad value is: \'(\w+)\'/';
    const MISSING_KEY_REGEX = '/Missing required key in single structure: (\w+)/';
    const UNEXPECTED_KEYS_REGEX = '/Unexpected keys \(([^\)]+)\) detected in parameter array/';
    const KEY_ERROR_REGEX = '/(.+?) => (.+?): (.+)/';
    const INVALID_PARAM_VALUE_REGEX = '/Invalid parameter value detected\s*\((.*?)\)/s';

    public function __construct(string $message, int $status = 422, ?\Throwable $previous = null){
        parent::__construct($message, $status, $previous);
    }

    public static function fromException(Exception $ex, bool $addAsPrevious = false) : static {
        if($ex instanceof invalid_parameter_exception){
            return self::fromInvalidParameterException($ex);
        }
        
        return parent::fromException($ex, $addAsPrevious);
    }

    protected static function fromInvalidParameterException(invalid_parameter_exception $ex, bool $addAsPrevious = false) : static {
        $previous = $addAsPrevious ? $ex->getPrevious() : null;

        if(preg_match(self::TYPE_MISMATCH_REGEX, $ex->debuginfo, $matches)){
            $message = get_string('validation:type_mismatch', 'local_lfapi', (object) [
                'value' => $matches[1],
                'key' => $matches[2],
            ]);
            return new static($message, 422, $previous);
        }
    
        if(preg_match(self::MISSING_KEY_REGEX, $ex->debuginfo, $matches)){
            $message = get_string('validation:missing_key', 'local_lfapi', (object) [
                'key' => $matches[1],
            ]);
            return new static($message, 400, $previous);
        }
    
        if(preg_match(self::UNEXPECTED_KEYS_REGEX, $ex->debuginfo, $matches)){
            $key = explode(',', $matches[1]);
            $message = get_string('validation:unexpected_key', 'local_lfapi', (object) [
                'key' => reset($key),
            ]);
            return new static($message, 422, $previous);
        }       
    
        if (preg_match(self::KEY_ERROR_REGEX, $ex->debuginfo, $matches)) {
            $message = get_string('validation:key_error', 'local_lfapi', (object) [
                'key' => $matches[1],
                'message' => $matches[2],
            ]);
            return new static($message, 422, $previous);
        }
            
        if(preg_match(self::INVALID_ARRAY_REGEX, $ex->debuginfo, $matches)){
            $message = get_string('validation:invalid_array', 'local_lfapi', (object) [
                'bad_value' => $matches[1],
            ]);
            return new static($message, 400, $previous);
        }
    
        return new static($ex->debuginfo, 422, $previous);
    }
}