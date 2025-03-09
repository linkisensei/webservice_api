<?php namespace webservice_api\hook\callbacks;

;

/**
 * Adapting the plugin for Moodle 4.3+ Hooks
 */
class general_hook_callbacks {

    /**
     * Making sure vendors are available for other plugins
     *
     * @param \core\hook\after_config $hook
     * @return void
     */
    public static function autoload_vendors_after_config($hook){
        global $CFG;
        require_once($CFG->dirroot . '/webservice/api/vendor/autoload.php');
    }
}
