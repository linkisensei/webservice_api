<?php

function xmldb_local_api_install() {
    global $CFG;
    require_once($CFG->dirroot . '/local/api/classes/config.php');
    \local_api\config::generate_secrets();
    return true;
}
