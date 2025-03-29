<?php

require_once(__DIR__ . '/../../../config.php');

$PAGE->set_context(context_system::instance());

$config = \webservice_api\config::instance();

if(!$config->is_swagger_enabled()){
    http_response_code(403);
    exit();
}

$data = [
    'title' => "$SITE->fullname API",
    'openapi_file_url' => (new moodle_url('/webservice/api/docs/openapi.json'))->out(false),
    'default_models_expand_depth' => $config->show_schemas_on_swagger() ? '1' : '-1',
];

echo $OUTPUT->render_from_template('webservice_api/docs/swagger', $data);