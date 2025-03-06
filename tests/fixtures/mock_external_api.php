<?php namespace webservice_api\fixtures;

use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_multiple_structure;
use \core_external\external_single_structure;
use \core_external\external_value;

class mock_external_api extends external_api {

    public static function mock_method_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'ID'),
            'user' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'email' => new external_value(PARAM_EMAIL, 'Username', VALUE_DEFAULT, 'test@email.com'),
            ]),
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Course ID'),
                    'fullname' => new external_value(PARAM_TEXT, 'Course name'),
                ])
            ),
        ]);
    }

    public static function mock_method($id, $user, $courses = []) {
        $params = self::validate_parameters(self::mock_method_parameters(), [
            'id' => $id,
            'user' => $user,
            'courses' => $courses,
        ]);

        return [
            'id' => $params['id'],
            'status' => 'success',
            'user' => $params['user'],
            'courses' => $params['courses'],
        ];
    }
}
