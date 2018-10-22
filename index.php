<?php
set_time_limit(0);
include_once('config.php');
include_once('src/functions.php');

// Debug check
if ($debug == 'true') {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}


// Connection to all virtual servers
foreach ($virtualserver_port as $port) {
	try {
	    $ts3 = TeamSpeak3::factory("serverquery://".$login_name.":".$login_password."@".$ip.":".$query_port."/?server_port=".$port."&nickname=R4P3&blocking=0");

	    makeSnapshot($mode,$port);

	} catch (Exception $e) {
	    echo 'Something went wrong: ',  $e->getMessage(), "\n";
	}

}





















?>