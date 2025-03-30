# 🚀 REST API Plugin for Moodle

## 🌟 Overview

This plugin provides a RESTful webservice API for Moodle, implementing OAuth2 authentication, rate limiting, logging, and OpenAPI documentation. It allows external systems to interact securely with Moodle's data and functionalities.

> This plugin is focused on `Moodle 4.2` and is does not takes into consideration new features like router or DI. It can be implemented in the future, but it seens that `Moodle 5.0+` will have a robust implementation for [API routes](https://moodledev.io/docs/5.0/apis/subsystems/routing) (It already exists on 4.5, but it will probably be refined).

## 🔥 Features

- ✅ OAuth2 authentication (Password Grant, Client Credentials, and Refresh Token)
- 🔑 JWT-based access and refresh tokens
- ⏳ Rate limiting per user
- 📜 Logging of API requests and authentication failures
- 📖 OpenAPI documentation generation
- ⚡ Middleware-based request handling

## 📥 Installation

1. **Clone the repository into the Moodle plugin directory:**

   ```bash
   cd /path/to/moodle/webservice
   git clone https://github.com/linkisensei/webservice_api.git api
   ```

2. **Run Moodle upgrade script:**

   ```bash
   php admin/cli/upgrade.php
   ```

3. **Configure settings:**

   - Navigate to `Site Administration > Plugins > Webservices > Webservice API`
   - Set the JWT secret keys and token expiration values
   - Enable the required authentication methods

### 🗄️ Webserver Configuration

To properly handle API requests, your webserver must be configured to route all non-static requests to `index.php`.

#### Apache (.htaccess)

For Apache users, add the following `.htaccess` file inside the `webservice/api` directory:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

#### Nginx

For Nginx, you need to add the following configuration inside your server block:

```nginx
location /webservice/api/ {
    try_files $uri /webservice/api/index.php?$query_string;
}
```

#### Caddy

For Caddy users, add the following rule to your `Caddyfile`:

```caddy
route /webservice/api/* {
    try_files {path} /webservice/api/index.php?{query}
}
```

#### Lighttpd

For Lighttpd users, add the following rewrite rule:

```lighttpd
url.rewrite-if-not-file = (
    "^/webservice/api/(.*)$" => "/webservice/api/index.php?$1"
)
```

**These configurations ensure that all API requests are properly routed to index.php, allowing the plugin to handle them correctly.**

## ⚙️ Configuration

### 🛠️ Plugin settings and variables

Some settings can be configured via `config.php`:

```php
$CFG->webservice_api = [
    'jwt_secret' => 'your-secret-key',
    'jwt_ttl' => 3600,
    'jwt_refresh_secret' => 'your-refresh-secret-key',
    'jwt_refresh_ttl' => 86400,
    'swagger_enabled' => true,
    'swagger_show_schemas' => false,
];

// Although, the jwt secrets are automatically generated during install
```

### 🔗 API Endpoints

#### 🔐 OAuth2 Authentication

- `POST /oauth2/token` - Issue access tokens
- `POST /oauth/credentials` - Create client credentials
- `PATCH /oauth/credentials/{client_id}` - Update credentials
- `DELETE /oauth/credentials/{client_id}` - Delete credentials

### 📄 OpenAPI Documentation

The API documentation is automatically generated and can be accessed at:

```
/webservice/api/docs
```

*You can provide documentation for your own routes by using annotations or attributes on the controllers*
`@see classes/controllers/auth/oauth2_controller.php`

#### Aditional documentation
If you want to define schemas outside of your controllers, that are loaded automatically, you will need to define a callback on your lib.php.

```php
/**
 * Returns an array of paths (to files or directories) that
 * contain OpenAI annotations or attributes that must be
 * included in the automatically generated documentation.
 * 
 * @return string[]
 */
function PLUGIN_NAME_webservice_api_openapi_definitions(){
    global $CFG;

    return [
       "$CFG->dirroot/plugin/name/classes/openapi/schemas",
       "$CFG->dirroot/plugin/name/classes/responses",
       // etc
    ];
}
```

## 📚 Dependencies & Credits

This plugin uses the following libraries:

- **[league/route](https://github.com/thephpleague/route)** (v6.2) - Fast routing library
- **[laminas/laminas-diactoros](https://github.com/laminas/laminas-diactoros)** (v3.5) - PSR-7 HTTP message implementation
- **[laminas/laminas-httphandlerrunner](https://github.com/laminas/laminas-httphandlerrunner)** (v2.11) - PSR-15 HTTP handler runner
- **[zircote/swagger-php](https://github.com/zircote/swagger-php)** (v5.0) - OpenAPI annotations parser
- **[swagger-ui](https://github.com/swagger-api/swagger-ui)** (v5.20.0) - Interactive API documentation UI

Special thanks to the authors of these libraries for their contributions to the open-source community. 🙌

## 🔌 Adding Routes from Other Plugins

The `webservice_api` plugin allows other Moodle plugins to register their own routes dynamically. To do this, plugins should define a function named `PLUGINNAME_webservice_api_register_routes()` inside their `lib.php` file. This function will be automatically detected and executed during route initialization.

Example:

#### Moodle 4.1 to 4.2
```php
function PLUGIN_NAME_webservice_api_register_routes(\League\Route\Router $router) {
    $router->get('/custom-endpoint', function (\Psr\Http\Message\ServerRequestInterface $request) {
        return ['message' => 'Hello from custom plugin!'];
    });
}
```

Additionally, you can use the `route_manager::register()` function to add routes programmatically.

```php
use webservice_api\routing\route_manager;

// This function should be executed on every Moodle page. 
// Ideally, register it within a plugin callback such as PLUGIN_NAME_after_config().
route_manager::register(function($router){
    $router->post('/custom-plugin/action', [custom_plugin_controller::class, 'handle_action']);
    
});

// Alternatively, load routes from a file using:
route_manager::register_file('path/to/routes.php')
```

#### Moodle 4.3+
Register the following hook into your `db/hooks.php` file.
```php
$callbacks = [
    [
        'hook' => webservice_api\hook\callbacks\pre_route_handling::class,
        'callback' => YOUR_PLUGIN\hook_callbacks::class . '::register_routes_callback',
        'priority' => 500,
    ],
];
```

Example of callback

```php
<?php
namespace YOUR_PLUGIN;

use \webservice_api\hook\callbacks\pre_route_handling;

class hook_callbacks {
    public static function register_routes_callback(pre_route_handling $hook): void {
        if (during_initial_install() || !get_config('local_stuff', 'version')) {
            return; // Prevent listening while your plugin is uninstalled
        }

        $router = $hook->get_router();
        $router->post('/your-plugin/action', [custom_plugin_controller::class, 'handle_action']);
    }
}
```

### </> Using the External API Adapter

To expose Moodle's native `external_api` functions via the `webservice_api`, you can use the `external_api_adapter`. This adapter allows you to map Moodle's built-in web service functions to API routes without manually defining request handling logic.

Example:

```php
use webservice_api\routing\adapters\external_api_adapter;
use webservice_api\routing\route_manager;

route_manager::register(function($router) {
    $router->post('/mod/assign/submit', new external_api_adapter('\mod_assign_external', 'submit_assignment'));
});
```

This approach ensures compatibility with existing Moodle web services while leveraging the structured API handling provided by `webservice_api`.

### 📂 Autoloading the Plugin's vendors

All vendors used in this plugin can be autoloaded, since it's autoload.php is included via the `_after_config()` callback.


This ensures that all necessary classes are properly loaded and available for use.

## 🛠️ Usage

### 🔑 Obtaining an Access Token

#### Password Grant:

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"grant_type": "password", "username": "admin", "password": "your-password"}' \
     https://yourmoodle.com/webservice/api/oauth2/token
```

#### Client Credentials Grant:

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"grant_type": "client_credentials", "client_id": "your-client-id", "client_secret": "your-client-secret"}' \
     https://yourmoodle.com/webservice/api/oauth2/token
```

### 📡 Calling an API Endpoint

```bash
curl -X GET \
     -H "Authorization: Bearer your-access-token" \
     https://yourmoodle.com/webservice/api/user/me
```

## 📋 Event Logging

The plugin logs the following events:

- `api_route_requested`: Logs each API request *(request_logger)*
- `api_auth_failed`: Logs authentication failures *(oauth2_token_auth)*
- `oauth2_credentials_created`: Logs credential creation
- `oauth2_credentials_updated`: Logs credential updates
- `oauth2_credentials_deleted`: Logs credential deletions

## 🛡️ Middleware

The plugin utilizes middleware for authentication, logging, and CORS handling:

- `oauth2_token_auth`: Validates JWT tokens
- `request_logger`: Logs API requests
- `cors_middleware`: Handles CORS headers
- `authenticated_rate_limit`: Enforces rate limits per user

*You can implement new middlewares following the [Route's middleware documentation](https://github.com/thephpleague/route/blob/6.x/docs/6.x/middleware.md).*

## 🤝 Contributing

If you'd like to contribute to the development of this plugin, please submit a pull request or open an issue on GitHub.

## 📜 License

This plugin is licensed under the GNU General Public License v3.0.
