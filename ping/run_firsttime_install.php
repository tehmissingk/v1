<?php
$dirname = dirname(__FILE__);
require_once($dirname.'/config.php');

$date = date('Y-m-d');
$time = time();
$key = 'm201A!1';

$query = "SELECT 1 FROM ".$table_prefix."unlock_check LIMIT 1";
$result = $mysqli->query($query);

if(empty($result)) {
	$sql = "CREATE TABLE ".$table_prefix."unlock_check (
	id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
	date DATE,
	unlock_key VARCHAR(255),
	created INT(11)
	)";
	$is_success = $mysqli->query($sql);
	
	if ($is_success) {
		echo 'create table \'unlock_check\': success<br />';
	
		/*$raw_key = sha1(base64_encode($date.$key));
		$raw_key = preg_replace("/[^0-9,.]/", "", $raw_key);
		$unlock_key = substr($raw_key,0,10);
		$mysqli->query("INSERT INTO ".$table_prefix."unlock_check (date, unlock_key,created) VALUES ('$date','$unlock_key','$time')");*/
	}
	else {
		echo 'create table \'unlock_check\': failed<br />';
	}
} else {
	echo 'table \'unlock_check\' exist: success<br />';
}
$query = "SELECT 1 FROM ".$table_prefix."ping_check LIMIT 1";
$result = $mysqli->query($query);

if(empty($result)) {
	$sql = "CREATE TABLE ".$table_prefix."ping_check (
	id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
	status INT(1),
	date DATE,
	ping_key VARCHAR(255),
	created INT(11)
	)";
	$is_success = $mysqli->query($sql);
	
	if ($is_success) {
		echo 'create table \'ping_check\': success<br />';
	}
	else {
		echo 'create table \'unlock_check\': failed<br />';
	}
	
} else {
	echo 'table \'ping_check\' exist: success<br />';
}

$status = 0;
if (ping("54.255.161.169")){ // lms.icarnegieasia.net
	echo 'ping to lms.icarnegieasia.net: success<br />';
	$status = 1;	// success ping
} else {
	echo 'ping to lms.icarnegieasia.net: failed<br />';
}

if ($status) {
	/*$ping_key = sha1(base64_encode($status.$date.$key));

	$check = $mysqli->query("SELECT * FROM ".$table_prefix."ping_check WHERE date='$date' LIMIT 1");
	if(!mysqli_num_rows($check)){
		$mysqli->query("INSERT INTO ".$table_prefix."ping_check (status, date, ping_key, created) VALUES ($status,'$date','$ping_key','$time')");
	} else{
		$row = $check->fetch_assoc();
		if(!$row['status'] AND $status){
			$mysqli->query("UPDATE ".$table_prefix."ping_check SET status='$status', ping_key='$ping_key', created='$time' WHERE id = $row[id]");
		}
	}*/
}

function ping($host,$port=80,$timeout=1){
    $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
    if ( ! $fsock ){
        return FALSE;
    } else {
        fclose($fsock);
        return TRUE;
    }
}

//$result->free();
$mysqli->close();