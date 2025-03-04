<?php namespace local_api\exceptions;

use \local_api\event\api_auth_failed;
use \local_api\exceptions\api_exception;

class auth_failure_exception extends api_exception {
    protected string $reason;
    protected array $other = [];

    public function __construct($errorcode, $module = '', $a = ''){
        parent::__construct($errorcode, $module, '', $a, null);
    }

    /**
     * Converts into a api_auth_failed event
     *
     * @return local_api\event\api_auth_failed
     */
    public function toEvent() : api_auth_failed {
        if(isset($this->reason)){
            $this->other['reason'] = $this->reason;
        }

        return api_auth_failed::create([
            'other' => $this->other,
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

    /**
     * Sets other information
     *
     * @param array $other
     * @return static
     */
    public function setOther(array $other) : static {
        $this->other = $other;
        return $this;
    }
}
