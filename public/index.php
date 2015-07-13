<?php
require '../vendor/autoload.php';
require_once '../app/middleware/APIResponseMiddleware.php';

$config = require_once '../app/config.php';
$app = new \Slim\Slim($config['slim']);
$app->add(new \middleware\APIResponseMiddleware());

$app->get('/', function() use($app, $config) {
    $app->response->setStatus(200);
    echo json_encode(array("name" => "conductor", "version" => $config['conductor']['version'], "timestamp" => date_timestamp_get(date_create())));
}); 

$app->get('/error', function() use($app) {
    $app->response->setStatus(404);
    echo json_encode(array("code" => 404, "message" => "Example 404 error.", "description" => "Just a test error."));
});

$app->notFound(function () use ($app) {
    $app->response->setStatus(404);
    echo json_encode(array("code" => 404, "message" => "404 Not Found", "description" => "Unable to locate requested resource."));
});

$app->run();