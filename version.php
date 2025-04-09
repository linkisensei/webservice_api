<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Webservice API plugin version file
 * 
 * This plugin should not be necessary after Moodle 5.0
 * @see https://moodledev.io/docs/5.0/apis/subsystems/routing
 *
 * @package   webservice_api
 * @author    Lucas Barreto
 * @license   GNU GPL v3 or later
 * @link https://github.com/linkisensei/webservice_api
 */

$plugin->component    = 'webservice_api';
$plugin->release      = '3.1';
$plugin->version      = 2025040900;
$plugin->requires     = 2023042400;
$plugin->supported    = [402, 404];
$plugin->maturity     = MATURITY_BETA;
