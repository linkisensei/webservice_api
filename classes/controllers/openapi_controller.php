<?php namespace webservice_api\controllers;

use \Psr\Http\Message\ServerRequestInterface;
use \webservice_api\controllers\abstract_controller;
use \webservice_api\services\openapi_documentation_service;
use \Laminas\Diactoros\Response\EmptyResponse;
use \Laminas\Diactoros\Response\HtmlResponse;
use \Laminas\Diactoros\Response\TextResponse;
use \context_system;
use \webservice_api\config;
use \webservice_api\helpers\routing\api_route_helper;

class openapi_controller extends abstract_controller {

    protected openapi_documentation_service $service;
    
    public function __construct() {
        parent::__construct();
        $this->service = new openapi_documentation_service();
    }

    public function get_openapi_file(ServerRequestInterface $request, array $args = []){
        $format = $this->optional_param($args, 'format', PARAM_TEXT, $this->service::FORMAT_JSON);
        $content = $this->service->get($format);
        $response = new TextResponse($content);
        return $response->withHeader('Content-Type', $this->service->get_content_type($format));
    }

    /**
     * Not being used.
     * 
     * @see /webservice/api/docs/index.php
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @return void
     */
    public function serve_swagger(ServerRequestInterface $request, array $args = []){
        global $PAGE, $OUTPUT, $SITE;

        $config = config::instance();

        if(!$config->is_swagger_enabled()){
            return new EmptyResponse(403);
        }

        $PAGE->set_context(context_system::instance());

        $data = [
            'title' => "$SITE->fullname API",
            'openapi_file_url' => api_route_helper::get_api_absolute_uri('/docs/openapi.json'),
            'default_models_expand_depth' => $config->show_schemas_on_swagger() ? '1' : '-1',
        ];

        $content = $OUTPUT->render_from_template('webservice_api/docs/swagger', $data);
        return new HtmlResponse($content);
    }

    /**
     * Redirects the user to the same route with "index.php" explicitly included in the path.
     *
     * In some server environments, the URL rewriting configuration used by the API
     * may prevent automatic redirection to the index.php file. This method ensures
     * compatibility by explicitly redirecting to the correct entry point.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The incoming HTTP request.
     * @return \Laminas\Diactoros\Response\RedirectResponse A redirect response to the updated path.
     */
    public function redirect_to_index(ServerRequestInterface $request){
        $uri = $request->getUri();

        $path = rtrim($uri->getPath(), '/');

        if(!str_ends_with($path, 'index.php')){
            $path .= '/index.php';
        }

        $newUri = $uri
            ->withPath($path)
            ->withQuery('');

        return new \Laminas\Diactoros\Response\RedirectResponse((string)$newUri, 302);
    }
}
