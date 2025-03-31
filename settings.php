<?php

defined('MOODLE_INTERNAL') || die;

if($hassiteconfig){
    if ($ADMIN->fulltree) {
        $name = \webservice_api\config::SETTING_JWT_TTL;
        $settings->add(
            new admin_setting_configduration(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                HOURSECS
            )
        );

        $name = \webservice_api\config::SETTING_JWT_REFRESH_TTL;
        $settings->add(
            new admin_setting_configduration(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                DAYSECS
            )
        );
 
        $settings->add(
            new admin_setting_heading(
                'webservice_api/documentation_settings_header',
                new lang_string('settings:documentation_header','webservice_api'),
                ''
            )
        );

        $name = \webservice_api\config::SETTING_SWAGGER_ENABLED;
        $settings->add(
            new admin_setting_configcheckbox(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                1
            )
        );

        $name = \webservice_api\config::SETTING_SWAGGER_SHOW_SCHEMAS;
        $settings->add(
            new admin_setting_configcheckbox(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                0
            )
        );
    }
}

