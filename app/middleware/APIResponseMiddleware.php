<?php
namespace middleware;

class APIResponseMiddleware extends \Slim\Middleware {

	protected $config;

    public function __construct($config) {
        $this->config = $config;
    }
 
    public function call() {
        $app = $this->app;
        $res = $app->response();

        $app->response()->headers->set('Content-Type', 'application/json');
        $res->headers->set('X-Api-Version', $this->config['conductor']['version']);

        $this->next->call();
    }
}