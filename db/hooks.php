<?php

defined('MOODLE_INTERNAL') || die();

$callbacks = [];

if(class_exists('core\hook\after_config')){
    $callbacks[] = [
        'hook' => core\hook\after_config::class,
        'callback' => webservice_api\hooks\callbacks::class . '::autoload_vendors_after_config',
        'priority' => 10000,
    ];
}