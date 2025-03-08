<?php declare(strict_types=1);

// Loading libraries and core
require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../config.php');

$service = new \webservice_api\services\openapi_documentation_service();
$service->generate_and_serve();