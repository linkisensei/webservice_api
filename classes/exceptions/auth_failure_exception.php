<?php namespace local_api\exceptions;

use \local_api\event\api_auth_failed;
use \local_api\exceptions\api_exception;

class auth_failure_exception extends api_exception {
    protected string $reason;

    /**
     * Converts into a api_auth_failed event
     *
     * @return local_api\event\api_auth_failed
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
