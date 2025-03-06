<?php

defined('MOODLE_INTERNAL') || die;

if($hassiteconfig){
    $settingspage = new admin_settingpage(
        'manage_webservice_api',
        new lang_string('settings:manage_title', 'webservice_api')
    );

    if ($ADMIN->fulltree) {
        $name = \webservice_api\config::SETTING_JWT_TTL;
        $settingspage->add(
            new admin_setting_configduration(
                $name,
                new lang_string("settings:$name", 'webservice_api'),
                '',
                HOURSECS
            )
        );

        $name = \webservice_api\config::SETTING_JWT_REFRESH_TTL;
        $settingspage->add(
            new admin_setting_configduration(
                $name,
                new lang_string("settings:$name", 'webservice_api'),
                '',
                DAYSECS
            )
        );
    }
    
    $ADMIN->add('webservicesettings', $settingspage);
}

