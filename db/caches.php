<?php

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'openapi' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'canuselocalstore' => false,
        'ttl' => DAYSECS,
    ],
];
