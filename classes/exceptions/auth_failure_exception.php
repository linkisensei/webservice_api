<?php namespace webservice_api\exceptions;

use \webservice_api\event\api_auth_failed;
use \webservice_api\exceptions\api_exception;

class auth_failure_exception extends api_exception {
    protected string $reason;

    public function __construct(string $message, int $status = 401, ?\Throwable $previous = null){
        parent::__construct($message, $status, $previous);
    }

    /**
     * Converts into a api_auth_failed event
     *
     * @return webservice_api\event\api_auth_failed
     */
    public function toEvent() : api_auth_failed {
        if(isset($this->reason)){
            $this->debug['reason'] = $this->reason;
        }

        return api_auth_failed::create([
            'other' => $this->debug,
        ]);
    }

    /**
     * Sets reason
     *
     * @param string $reason
     * @return static
     */
    public function setReason(string $reason) : static {
        $this->reason = $reason;
        return $this;
    }

    
}
