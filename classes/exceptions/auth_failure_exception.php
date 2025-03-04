<?php namespace local_api\exceptions;

use \core\event\webservice_login_failed;

class auth_failure_exception extends \moodle_exception {

    protected string $reason;
    protected array $other = [];

    public function __construct($errorcode, $module = '', $reason = '', $other = []) {
        parent::__construct($errorcode, $module);
        $this->reason = $reason;
        $this->other = $other;
        $this->other['reason'] = $reason;
        $this->other['source'] = 'local_api';
    }

    public function to_event() : webservice_login_failed {
        return webservice_login_failed::create([
            'other' => $this->other,
        ]);
    }

    public function export() : object {
        return (object) [
            'message' => $this->getMessage(),
        ];
    }
}
