<?php

defined('MOODLE_INTERNAL') || die;

if($hassiteconfig){
    $settingspage = new admin_settingpage(
        'webservicesettingapi',
        new lang_string('settings:manage_title', 'webservice_api')
    );

    if ($ADMIN->fulltree) {
        $name = \webservice_api\config::SETTING_JWT_TTL;
        $settingspage->add(
            new admin_setting_configduration(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                HOURSECS
            )
        );

        $name = \webservice_api\config::SETTING_JWT_REFRESH_TTL;
        $settingspage->add(
            new admin_setting_configduration(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                DAYSECS
            )
        );
 
        $settingspage->add(
            new admin_setting_heading(
                'webservice_api/documentation_settings_header',
                new lang_string('settings:documentation_header','webservice_api'),
                ''
            )
        );

        $name = \webservice_api\config::SETTING_SWAGGER_ENABLED;
        $settingspage->add(
            new admin_setting_configcheckbox(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                1
            )
        );

        $name = \webservice_api\config::SETTING_SWAGGER_SHOW_SCHEMAS;
        $settingspage->add(
            new admin_setting_configcheckbox(
                "webservice_api/$name",
                new lang_string("settings:$name", 'webservice_api'),
                '',
                0
            )
        );
    }
    
    $ADMIN->add('webservicesettings', $settingspage);
}

