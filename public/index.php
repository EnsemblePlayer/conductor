<?php
require '../vendor/autoload.php';
require_once '../app/middleware/APIResponseMiddleware.php';

$config = require_once '../app/config.php';
$app = new \Slim\Slim($config['slim']);
$app->add(new \middleware\APIResponseMiddleware($config));
$m = mysqli_connect("localhost","root","teotauy18","secondEnsemble");	//establish connection to database
if (!$m) {
    echo json_encode(array("code" => 500, "message" => "Could not connect", "description" => "Unable to locate requested resource.")); //could not connect to database
}

$app->get('/', function() use($app, $config) {
    $app->response->setStatus(200);
    echo json_encode(array("name" => "conductor", "version" => $config['conductor']['version']));
});

$app->get('/users', function() use($app, $config, $m) { //return all users
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `userData` ORDER BY `userId`") or die($m->error);
	$users = array();
	while($arr = $s->fetch_array(MYSQLI_ASSOC)){
		$u = array();
		$u['userId'] = $arr['userId'];
		$u['username']= $arr['username'];
		$users[] = $u;
	}
	echo json_encode($users);
});	

$app->get('/users/:id', function($id) use($app, $config, $m) { //return user with :id
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

$app->get('/rooms', function() use($app, $config, $m) { //return all rooms
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomData` ORDER BY `roomId`") or die($m->error);
	$rooms = array();
	while($arr = $s->fetch_array(MYSQLI_ASSOC)){
		unset($arr['password']);
		$rooms[] = $arr;
	}
	echo json_encode($rooms);
});

$app->get('/rooms/:id', function($id) use($app, $config, $m) { //return room with :id
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

$app->get('/users/:user/rooms', function($user) use($app, $config, $m) { //return rooms user has access to
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomPerms` WHERE `userId`='$user' ORDER BY `roomId`") or die($m->error);
	if($s->num_rows>=1){
		$rooms = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$rooms[] = $arr;
		}
		echo json_encode($rooms);
	} else {
		echo json_encode(array("code" => 204, "message" => "User does not belong to any rooms", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/playlists', function() use($app, $config, $m) { //return all playlists
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistData` ORDER BY `playlistId`") or die($m->error);
	$pls = array();
	while($arr = $s->fetch_array(MYSQLI_ASSOC)){
		$pls[] = $arr;
	}
	echo json_encode($pls);
});

$app->get('/playlists/:id', function($id) use($app, $config, $m) { //return playlist with :id
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistData` WHERE `playlistId`='$id' ORDER BY `playlistId`") or die($m->error);
	if($s->num_rows==1){
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		echo json_encode($arr);
	} else {
		echo json_encode(array("code" => 204, "message" => "Playlist doesn't exist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/users/:user/playlists', function($user) use($app, $config, $m) { //return all playlists user has access to
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistPerms` WHERE `userId`='$user' ORDER BY `playlistId`") or die($m->error);
	if($s->num_rows>=1){
		$pls = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$pls[] = $arr;
		}
		echo json_encode($pls);
	} else {
		echo json_encode(array("code" => 204, "message" => "User does not belong to any playlists", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/rooms/:room/songs', function($room) use($app, $config, $m) { //return all songs in room
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomSongs` WHERE `roomId`='$room' ORDER BY `position`") or die($m->error);
	if($s->num_rows>=1){
		$songs = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$songs[] = $arr;
		}
		echo json_encode($songs);
	} else {
		echo json_encode(array("code" => 204, "message" => "There are no songs in this room", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/rooms/:room/songs/:id', function($room,$id) use($app, $config, $m) { //return all songs in room with :id
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomSongs` WHERE `roomId`='$room'AND`songID`='$id' ORDER BY `position`") or die($m->error);
	if($s->num_rows>=1){
		$songs = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$songs[] = $arr;
		}
		echo json_encode($songs);
	} else {
		echo json_encode(array("code" => 204, "message" => "There are no specified songs in this room", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/playlists/:playlist/songs', function($pl) use($app, $config, $m) { //return all songs in playlist
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistSongs` WHERE `playlistId`='$pl' ORDER BY `position`") or die($m->error);
	if($s->num_rows>=1){
		$songs = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$songs[] = $arr;
		}
		echo json_encode($songs);
	} else {
		echo json_encode(array("code" => 204, "message" => "There are no songs in this playlist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/playlists/:playlist/songs/:id', function($pl,$id) use($app, $config, $m) { //return all songs in playlist with :id
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistSongs` WHERE `playlistId`='$pl'AND`songID`='$id' ORDER BY `position`") or die($m->error);
	if($s->num_rows>=1){
		$songs = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$songs[] = $arr;
		}
		echo json_encode($songs);
	} else {
		echo json_encode(array("code" => 204, "message" => "There are no specified songs in this playlist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/rooms/:room/permissions', function($room) use($app, $config, $m) { //return all permissions for room with :room
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomPerms` WHERE `roomId`='$room' ORDER BY `userId`") or die($m->error);
	if($s->num_rows>=1){
		$u = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$u[] = $arr;
		}
		echo json_encode($u);
	} else {
		echo json_encode(array("code" => 204, "message" => "That room has no users associated with it", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/rooms/:room/permissions/:user', function($room,$user) use($app, $config, $m) { //return all permissions for room with :room and user with :id
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `roomPerms` WHERE `roomId`='$room'AND`userId`='$user' ORDER BY `userId`") or die($m->error);
	if($s->num_rows>=1){
		$u = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$u[] = $arr;
		}
		echo json_encode($u);
	} else {
		echo json_encode(array("code" => 204, "message" => "That room has no permissions for the specified user", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/playlists/:playlist/permissions', function($pl) use($app, $config, $m) { //return all permissions for playlist with :pl
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistPerms` WHERE `playlistId`='$pl' ORDER BY `userId`") or die($m->error);
	if($s->num_rows>=1){
		$u = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$u[] = $arr;
		}
		echo json_encode($u);
	} else {
		echo json_encode(array("code" => 204, "message" => "That playlist has no users associated with it", "description" => "Unable to locate requested resource.")); 
	}
});

$app->get('/playlists/:playlist/permissions/:user', function($pl,$user) use($app, $config, $m) { //return all permissions for playlist with :pl and user with :id
	$app->response->setStatus(200);
	$s = $m->query("SELECT * FROM `playlistPerms` WHERE `playlistId`='$pl'AND`userId`='$user' ORDER BY `userId`") or die($m->error);
	if($s->num_rows>=1){
		$u = array();
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			unset($arr['uniqueId']);
			$u[] = $arr;
		}
		echo json_encode($u);
	} else {
		echo json_encode(array("code" => 204, "message" => "That playlist has no permissions for the specified user", "description" => "Unable to locate requested resource.")); 
	}
});

$app->post('/rooms', function () use ($app, $config, $m) { //creates new room
    $name = $app->request->post('name');
	$password = $app->request->post('password');
	$userId = $app->request->post('userId');
    $m->query("INSERT INTO `roomData` (`name`,`password`,`userId`) VALUES ('$name','$password','$userId')")or die($m->error);
	$roomId = $m->insert_id;
	$m->query("INSERT INTO `roomPerms` (`level`,`roomId`,`userId`) VALUES ('4','$roomId','$userId')")or die($m->error);
	$app->response->setStatus(201);
});

$app->post('/rooms/:id', function ($id) use ($app, $config, $m) { //copies room with :id to new room (songs, perms, data)
    $name = $app->request->post('name');
	$password = $app->request->post('password');
	$userId = $app->request->post('userId');
	$roomId = $m->insert_id;
	$m->query("INSERT INTO `roomPerms` (`level`,`roomId`,`userId`) VALUES ('4','$roomId','$userId')") or die($m->error);
	$s = $m->query("SELECT * FROM `roomPerms` WHERE `roomId`='$id' ORDER BY `userId`") or die($m->error);
	if($s->num_rows>=1){
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			$u = $arr['userId'];
			$l = $arr['level'];
			$m->query("INSERT INTO `roomPerms` (`userId`,`level`,`roomId`) VALUES ('$u','$l','$roomId')") or die($m->error);
		}
		$m->query("INSERT INTO `roomData` (`name`,`password`,`userId`) VALUES ('$name','$password','$userId')") or die($m->error);
		$s = $m->query("SELECT * FROM `roomSongs` WHERE `roomId`='$id' ORDER BY `userId`") or die($m->error);
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			$u = $arr['userId'];
			$l = $arr['position'];
			$p = $arr['priority'];
			$sid = $arr['songId'];
			$m->query("INSERT INTO `roomSongs` (`userId`,`position`,`roomId`,`priority`,`songId`) VALUES ('$u','$l','$roomId','$p','$sid')") or die($m->error);
		}	
		$app->response->setStatus(201);
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "The specified room does not exist", "description" => "Unable to locate requested resource.")); 
	}	
});

$app->post('/playlists', function () use ($app, $config, $m) { //creates new playlist
    $name = $app->request->post('name');
	$userId = $app->request->post('userId');
    $m->query("INSERT INTO `playlistData` (`name`,`userId`) VALUES ('$name','$userId')")or die($m->error);
	$playlistId = $m->insert_id;
	$m->query("INSERT INTO `playlistPerms` (`level`,`playlistId`,`userId`) VALUES ('4','$playlistId','$userId')")or die($m->error);
	$app->response->setStatus(201);
});

$app->post('/playlists/:id', function ($id) use ($app, $config, $m) { //copies playlist with :id to new playlist (songs, perms, data)
    $name = $app->request->post('name');
	$userId = $app->request->post('userId');
	$playlistId = $m->insert_id;
	$m->query("INSERT INTO `playlistPerms` (`level`,`playlistId`,`userId`) VALUES ('4','$playlistId','$userId')") or die($m->error);
	$s = $m->query("SELECT * FROM `playlistPerms` WHERE `playlistId`='$id' ORDER BY `userId`") or die($m->error);
	if($s->num_rows>=1){
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			$u = $arr['userId'];
			$l = $arr['level'];
			$m->query("INSERT INTO `playlistPerms` (`userId`,`level`,`playlistId`) VALUES ('$u','$l','$playlistId')") or die($m->error);
		}
		$m->query("INSERT INTO `playlistData` (`name`,`userId`) VALUES ('$name','$userId')") or die($m->error);
		$s = $m->query("SELECT * FROM `playlistSongs` WHERE `playlistId`='$id' ORDER BY `userId`") or die($m->error);
		while($arr = $s->fetch_array(MYSQLI_ASSOC)){
			$u = $arr['userId'];
			$l = $arr['position'];
			$p = $arr['priority'];
			$sid = $arr['songId'];
			$m->query("INSERT INTO `playlistSongs` (`userId`,`position`,`playlistId`,`priority`,`songId`) VALUES ('$u','$l','$playlistId','$p','$sid')") or die($m->error);
		}	
		$app->response->setStatus(201);
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "The specified playlist does not exist", "description" => "Unable to locate requested resource.")); 
	}
});

//TODO CONFIRM USER EXISTS; CONFIRM SONG EXISTS
$app->post('/rooms/:room/songs', function ($roomId) use ($app, $config, $m) { //adds song to room
    $s = $m->query("SELECT * FROM `roomData` WHERE `roomId`='$roomId' ORDER BY `roomId`") or die ($m->error);
	if($s->num_rows>=1){
		$priority = $app->request->post('priority');
		$userId = $app->request->post('userId');
		$songId = $app->request->post('songId');
		$position = $app->request->post('position');
		//change position
		$m->query("INSERT INTO `roomSongs` (`priority`,`position`,`userId`,`songId`,`roomId`) VALUES ('$priority','$position','$userId','$songId','$roomId')")or die($m->error);
		$app->response->setStatus(201);
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "The specified room does not exist", "description" => "Unable to locate requested resource.")); 
	}
});
//this method probably has no viablue use currently - may need to rethink how this path should be and the contents of this method
$app->post('/rooms/:room/songs/:id', function ($roomId,$sid) use ($app, $config, $m) { //copies song with :id from room :room back to room :room
	$s = $m->query("SELECT * FROM `roomSongs` WHERE `songId`='$sid'AND`roomId`='$roomId' ORDER BY `position` LIMIT 1") or die($m->error);
	if($s->num_rows>=1){
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		$priority = $arr['priority'];
		$userId = $app->request->post('userId');
		$position = $app->request->post('position');
		//change position
		$app->response->setStatus(201);
		$m->query("INSERT INTO `roomSongs` (`priority`,`position`,`userId`,`songId`,`roomId`) VALUES ('$priority','$position','$userId','$sid','$roomId')")or die($m->error);
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "There are no specified songs in this room", "description" => "Unable to locate requested resource.")); 
	}
});

$app->post('/playlists/:playlist/songs', function ($playlistId) use ($app, $config, $m) { //adds song to playlist
    $s = $m->query("SELECT * FROM `playlistData` WHERE `playlistId`='$playlistId' ORDER BY `playlistId`") or die ($m->error);
	if($s->num_rows>=1){
		$priority = $app->request->post('priority');
		$userId = $app->request->post('userId');
		$songId = $app->request->post('songId');
		$position = $app->request->post('position');
		//change position
		$app->response->setStatus(201);
		$m->query("INSERT INTO `playlistSongs` (`priority`,`position`,`userId`,`songId`,`playlistId`) VALUES ('$priority','$position','$userId','$songId','$playlistId')")or die($m->error);
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "The specified playlist does not exist", "description" => "Unable to locate requested resource.")); 
	}
});
//see comment on rooms/:room/songs/:id for thoughts on the viability of this method
$app->post('/playlists/:playlist/songs/:id', function ($playlistId,$sid) use ($app, $config, $m) { //copies song with :id from playlist :pl back to playlist :pl
	$s = $m->query("SELECT * FROM `playlistSongs` WHERE `songId`='$sid'AND`playlistId`='$playlistId' ORDER BY `position` LIMIT 1") or die($m->error);
	if($s->num_rows>=1){
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		$priority = $arr['priority'];
		$userId = $app->request->post('userId');
		$position = $app->request->post('position');
		$app->response->setStatus(201);
		//change position
		$m->query("INSERT INTO `playlistSongs` (`priority`,`position`,`userId`,`songId`,`playlistId`) VALUES ('$priority','$position','$userId','$sid','$playlistId')")or die($m->error);
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "There are no specified songs in this playlist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->post('/rooms/:room/permissions', function ($roomId) use ($app, $config, $m) { //add permissions to room :id
	$s = $m->query("SELECT * FROM `roomData` WHERE `roomId`='$roomId' ORDER BY `roomId`") or die ($m->error); //check if room exists
	if($s->num_rows>=1){
		$userId = $app->request->post('userId');
		$level = $app->request->post('level');
		
		$s = $m->query("SELECT * FROM `roomPerms` WHERE `roomId`='$roomId'AND`userId`='$userId' ORDER BY `level` LIMIT 1") or die ($m->error); //grab highest current permissions
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		if($level > $arr['level']){
			$s = $m->query("DELETE FROM `roomPerms` WHERE `roomId`='$roomId'AND`userId`='$userId'") or die ($m->error); //delete all entries for room with user
			$app->response->setStatus(201);
			$m->query("INSERT INTO `roomPerms` (`userId`,`level`,`roomId`) VALUES ('$userId','$level','$roomId')")or die($m->error); //add permission with new level
		}
		else{
			$app->response->setStatus(400);
			echo json_encode(array("code" => 400, "message" => "The user already has same or higher permissions", "description" => "Unable to locate requested resource.")); 
		}
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "The specified room does not exist", "description" => "Unable to locate requested resource.")); 
	}
});

$app->post('/playlists/:playlist/permissions', function ($playlistId) use ($app, $config, $m) { //add permissions to playlists :id
	$s = $m->query("SELECT * FROM `playlistData` WHERE `playlistId`='$playlistId' ORDER BY `playlistId`") or die ($m->error); //check if playlist exists
	if($s->num_rows>=1){
		$userId = $app->request->post('userId');
		$level = $app->request->post('level');	
		$s = $m->query("SELECT * FROM `playlistPerms` WHERE `playlistId`='$playlistId'AND`userId`='$userId' ORDER BY `level` LIMIT 1") or die ($m->error); //grab highest current permissions
		$arr = $s->fetch_array(MYSQLI_ASSOC);
		if($level > $arr['level']){
			$s = $m->query("DELETE FROM `playlistPerms` WHERE `playlistId`='$playlistId'AND`userId`='$userId'") or die ($m->error); //delete all entries for room with user	
			$app->response->setStatus(201);
			$m->query("INSERT INTO `playlistPerms` (`userId`,`level`,`playlistId`) VALUES ('$userId','$level','$playlistId')")or die($m->error);
		}
		else{
			$app->response->setStatus(400);
			echo json_encode(array("code" => 400, "message" => "The user already has same or higher permissions", "description" => "Unable to locate requested resource.")); 
		}
	}
	else {
		$app->response->setStatus(400);
		echo json_encode(array("code" => 400, "message" => "The specified playlist does not exist", "description" => "Unable to locate requested resource.")); 
	}	
});

$app->delete('/rooms/:id', function ($roomId) use ($app, $config, $m) { //delete room with :id and all associated (Perms, Songs, Data) (NOT HISTORY)
    $m->query("DELETE FROM `roomData` WHERE `roomId`='$roomId'")or die($m->error);
	$m->query("DELETE FROM `roomPerms` WHERE `roomId`='$roomId'")or die($m->error);
	$m->query("DELETE FROM `roomSongs` WHERE `roomId`='$roomId'")or die($m->error);
	$app->response->setStatus(200);
});

$app->delete('/playlists/:id', function ($playlistId) use ($app, $config, $m) { //delete playlist with :id and all associated (Perms, Songs, Data)
    $m->query("DELETE FROM `playlistData` WHERE `playlistId`='$playlistId'")or die($m->error);
	$m->query("DELETE FROM `playlistPerms` WHERE `playlistId`='$playlistId'")or die($m->error);
	$m->query("DELETE FROM `playlistSongs` WHERE `playlistId`='$playlistId'")or die($m->error);
	$app->response->setStatus(200);
});

$app->delete('/rooms/:room/songs', function ($roomId) use ($app, $config, $m) { //delete all songs in room with :room
	$m->query("DELETE FROM `roomSongs` WHERE `roomId`='$roomId'")or die($m->error);
	$app->response->setStatus(200);
});

$app->delete('/rooms/:room/songs/:id', function ($roomId,$id) use ($app, $config, $m) { //delete all songs with :id in room with :room
	$m->query("DELETE FROM `roomSongs` WHERE `roomId`='$roomId'AND`songId`='$id'")or die($m->error);
	$app->response->setStatus(200);
});

$app->delete('/playlists/:playlist/songs', function ($playlistId) use ($app, $config, $m) { //delete all songs in playlist with :playlist
	$m->query("DELETE FROM `playlistSongs` WHERE `playlistId`='$playlistId'")or die($m->error);
	$app->response->setStatus(200);
});

$app->delete('/playlists/:playlist/songs/:id', function ($playlistId,$id) use ($app, $config, $m) { //delete all songs with :id in playlist with :playlist
	$m->query("DELETE FROM `playlistSongs` WHERE `playlistId`='$playlistId'AND`songId`='$id'")or die($m->error);
	$app->response->setStatus(200);
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