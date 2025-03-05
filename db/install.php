<?php

function xmldb_webservice_api_install() {
    global $CFG;
    require_once($CFG->dirroot . '/webservice/api/classes/config.php');
    \webservice_api\config::generate_secrets();
    return true;
}
