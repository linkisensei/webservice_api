# 🚀 Webservice API Plugin for Moodle

## 🌟 Overview

This plugin provides a RESTful webservice API for Moodle, implementing OAuth2 authentication, rate limiting, logging, and OpenAPI documentation. It allows external systems to interact securely with Moodle's data and functionalities.

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

### 🔄 Webserver Configuration

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

### 🔄 Using the External API Adapter

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

### 📂 Autoloading the Plugin

If another plugin needs to autoload the classes from this plugin's vendors, it should include the `bootstrap.php` file located in the root of `webservice/api`:

```php
require_once($CFG->dirroot . '/webservice/api/bootstrap.php');
```

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

## 🤝 Contributing

If you'd like to contribute to the development of this plugin, please submit a pull request or open an issue on GitHub.

## 📜 License

This plugin is licensed under the GNU General Public License v3.0.


