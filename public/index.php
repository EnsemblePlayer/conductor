<?php
require '../vendor/autoload.php';
require_once '../app/middleware/APIResponseMiddleware.php';

$config = require_once '../app/config.php';
$app = new \Slim\Slim($config['slim']);
$app->add(new \middleware\APIResponseMiddleware($config));
$m = mysqli_connect("localhost","root","teotauy18","secondEnsemble");
//$m->query("") or die($m->error);
if (!$m) {
    echo json_encode(array("code" => 500, "message" => "Could not connect", "description" => "Unable to locate requested resource."));
}

$app->get('/', function() use($app, $config) {
    $app->response->setStatus(200);
    echo json_encode(array("name" => "conductor", "version" => $config['conductor']['version'], "timestamp" => date_timestamp_get(date_create())));
});

$app->get('/users', function() use($app, $config, $m) {
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `userData` ORDER BY `userId`") or die($m->error);
	$users = array();
	while($arr = $s->fetch_array(MYSQLI_ASSOC)){
	//	unset($arr['password']);
	//	$users[] = $arr;
		$u = array();
		$u['userId'] = $arr['userId'];
		$u['username']= $arr['username'];
		$users[] = $u;
	}
	echo json_encode($users);
});	

$app->get('/users/:id', function($id) use($app, $config, $m) {
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `userData` WHERE `userId`='$id' ORDER BY `userId`") or die($m->error);
	if($s->num_rows==1){
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		$u = array();
		$u['userId'] = $arr['userId'];
		$u['username']= $arr['username'];
		echo json_encode($u);
	} else {
		echo json_encode(array("code" => 204, "message" => "User doesn't exist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/rooms', function() use($app, $config, $m) {
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomData` ORDER BY `roomId`") or die($m->error);
	$rooms = array();
	while($arr = $s->fetch_array(MYSQLI_ASSOC)){
		unset($arr['password']);
		$rooms[] = $arr;
	}
	echo json_encode($rooms);
});

$app->get('/rooms/:id', function($id) use($app, $config, $m) {
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomData` WHERE `roomId`='$id' ORDER BY `roomId`") or die($m->error);
	if($s->num_rows==1){
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		unset($arr['password']);
		echo json_encode($arr);
	} else {
		echo json_encode(array("code" => 204, "message" => "Room doesn't exist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/:user/rooms', function($user) use($app, $config, $m) {
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomPerms` WHERE `userId`='$user' ORDER BY `roomId`") or die($m->error);
	if($s->num_rows>=1){
		$rooms = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$rooms[] = $arr;
		}
		echo json_encode($arr);
	} else {
		echo json_encode(array("code" => 204, "message" => "User does not belong to any rooms", "description" => "Unable to locate requested resource.")); 
	}
});



$app->post('/logout', function() use($app) {
	// TODO: Revoke session key
    $app->response->setStatus(200);
});

$app->notFound(function () use ($app) {
    $app->response->setStatus(404);
    echo json_encode(array("code" => 404, "message" => "404 Not Found", "description" => "Unable to locate requested resource."));
});

$app->run();