<?php

require_once(__DIR__ . '/../../../config.php');

$PAGE->set_context(context_system::instance());

$data = [
    'title' => "$SITE->fullname API",
    'openapi_file_url' => (new moodle_url('/webservice/api/docs/openapi.json'))->out(false),
];

echo $OUTPUT->render_from_template('webservice_api/docs/swagger', $data);