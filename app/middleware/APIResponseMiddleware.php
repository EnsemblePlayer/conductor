<?php
namespace middleware;

class APIResponseMiddleware extends \Slim\Middleware {
 
    public function call() {
        $app = $this->app;
        $res = $app->response();

        $app->response()->headers->set('Content-Type', 'application/json');
        $res->headers->set('X-Api-Version', '1.0');

        $this->next->call();
    }
}