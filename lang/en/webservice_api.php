<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'REST API';


$string['event:api_auth_failed'] = 'API authentication failed';
$string['event:api_route_requested'] = 'API route requested';
$string['event:oauth2_credentials_created'] = 'Oauth2 credentials created';
$string['event:oauth2_credentials_updated'] = 'Oauth2 credentials updated';
$string['event:oauth2_credentials_deleted'] = 'Oauth2 credentials deleted';


$string['settings:manage_title'] = 'Manage REST API';
$string['settings:jwt_ttl'] = 'Token expires in';
$string['settings:jwt_refresh_ttl'] = 'Refresh token expires in';
$string['settings:swagger_enabled'] = 'Enable swagger';
$string['settings:swagger_show_schemas'] = 'Show schemas';
$string['settings:documentation_header'] = "Documentation and Swagger";


$string['api:use'] = 'access this API';
$string['api:managecredentials'] = 'manage client credentials';
$string['api:manageselfcredentials'] = 'manage your own client credentials';
$string['api:config'] = 'configure the API settings';


$string['docs:post_oauth2_token_summary'] = "Generate an access token";
$string['docs:post_oauth2_token_description'] = "Handles OAuth2 authentication and returns an access token.";
$string['docs:password_grant_request'] = "Password grant request";
$string['docs:client_credentials_grant_request'] = "Client credentials grant request";
$string['docs:refresh_token_grant_request'] = "Refresh token grant request";
$string['docs:access_token_generated_success'] = "Access token generated successfully";
$string['docs:invalid_request'] = "Invalid request";
$string['docs:invalid_credentials'] = "Invalid credentials";
$string['docs:bearer_auth_jwt_description'] = 'JWT access token must be included in the Authorization header';
$string['docs:oauth2_credentials_description'] = 'Endpoints for managing OAuth2 credentials';
$string['docs:create_credentials_summary'] = 'Create new OAuth2 credentials';
$string['docs:create_credentials_description'] = 'Generates credentials for a specific user.';
$string['docs:create_credentials_success'] = 'Credentials successfully created';
$string['docs:update_credentials_summary'] = 'Update OAuth2 credentials';
$string['docs:update_credentials_description'] = 'Regenerate the secret key for a specific client_id.';
$string['docs:update_credentials_success'] = 'Credentials successfully updated';
$string['docs:delete_credentials_summary'] = 'Revoke OAuth2 credentials';
$string['docs:delete_credentials_description'] = 'Remove OAuth2 credentials based on the client_id.';
$string['docs:delete_credentials_success'] = 'Credentials successfully revoked';
$string['docs:credentials_not_found'] = 'Credentials not found';
$string['docs:invalid_parameters'] = 'Invalid parameters';
$string['docs:expires_at_description'] = 'Optional timestamp for credential expiration';


$string['exception:invalid_access_token_secret'] = 'Access token secret not configured';
$string['exception:missing_grant_type'] = "Missing grant_type";
$string['exception:invalid_user_credentials'] = "Invalid user credentials";
$string['exception:missing_required_key'] = 'Missing required \"{$a}\"';
$string['exception:empty_key'] = 'Empty \"{$a}\"';
$string['exception:invalid_key'] = 'Invalid {$a}';
$string['exception:invalid_client_user'] = 'Invalid client user';
$string['exception:user_not_found'] = 'User not found';
$string['exception:user_not_confirmed'] = 'User not confirmed';
$string['exception:policy_not_agreed'] = 'Policy not agreed';
$string['exception:client_credentials_not_found'] = 'Client credentials not found!';
$string['exception:invalid_client_credentials'] = "Invalid client credentials";
$string['exception:expired_client_credentials'] = "Expired client credentials";
$string['exception:invalid_credentials_expiration'] = "Credentials expiration must be a future timestamp";
$string['exception:credentials_already_exist'] = 'Credentials already exist for the specified user';
$string['exception:pagination_limit_violation'] = 'The maximum allowed page size is {$a}';
$string['exception:invalid_timed_token'] = 'Expired access token.';
$string['exception:invalid_token'] = 'Missing or invalid access token.';