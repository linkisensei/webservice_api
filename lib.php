<?php

/**
 * Making sure vendors are available for other plugins
 * 
 * Utilizes a lib callback (Moodle 4.2 or less)
 *
 * @return void
 */
function webservice_api_after_config(){
    global $CFG;
    require_once($CFG->dirroot . '/webservice/api/vendor/autoload.php');
}
