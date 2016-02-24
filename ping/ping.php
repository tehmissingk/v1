<?php
$dirname = dirname(__FILE__);
require_once($dirname.'/config.php');

$date = date('Y-m-d');
$time = time();
$key = 'm201A!1';
$status = $ping_check = $unlock_check = 0;

if (ping("54.255.161.169")){ // lms.icarnegieasia.net
	echo 'ping to lms.icarnegieasia.net: success<br />';
	$status = 1;	// success ping
}

if ($result = $mysqli->query("SELECT * FROM ".$table_prefix."unlock_check ORDER BY date DESC")) {
	while ($row = $result->fetch_assoc()) {
		$unlock_key = gen_unlock_key($row['date'],$key);
		if($unlock_key==$row['unlock_key']){
			$unlock_date = $row['date'];
			$unlock_check = 1;
			break;
		}
	}
	$result->free();
	
	if ($unlock_check) {
		$ping_check = 1;
		$now = time(); // or your date as well
		$unlock_date = strtotime($unlock_date);
		$datediff = $now - $unlock_date;
		$datediff = floor($datediff/(60*60*24));
		//echo $datediff.'<br />';
		
		if($datediff >= 7){
			$seven_day = "'".date('Y-m-d', strtotime('-1 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-2 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-3 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-4 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-5 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-6 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-7 day', strtotime($date)))."'";
			
			$sql = "SELECT * FROM ".$table_prefix."ping_check WHERE date IN ($seven_day) AND status = 1 ORDER BY date DESC";
			if ($result = $mysqli->query($sql)) {
				$total_fake = 0;
				while ($row = $result->fetch_assoc()) {
					if($row['ping_key'] != gen_ping_key($row['status'],$row['date'],$key)) {
						$total_fake++;
					}
				}
				if (mysqli_num_rows($result)==$total_fake) {	// all ping is fake
					$ping_check = 0;
					//echo 'all fake<br />';
				}
			} else {	// no ping recorded ever
				$ping_check = 0;
				//echo 'no ping recorded ever<br />';
			}
		} else if ($datediff >= 0 AND $datediff < 7) {	// still below 7 days, should proceed
			$ping_check = 1;
		}
	}
}

if (!$unlock_check) {
	// never unlock
	//echo 'never unlock/invalid unlock key<br />';
}
if (!$ping_check) {
	// suspend login
	//echo 'suspend login'.$ping_check.'<br />';
} else if ($ping_check) {
	// proceed to insert data
	$ping_key = gen_ping_key($status,$date,$key);

	$check = $mysqli->query("SELECT * FROM ".$table_prefix."ping_check WHERE date='$date' LIMIT 1");
	if(!mysqli_num_rows($check)){
		$mysqli->query("INSERT INTO ".$table_prefix."ping_check (status, date, ping_key, created) VALUES ($status,'$date','$ping_key','$time')");
	} else{
		$row = $check->fetch_assoc();
		if(!$row['status'] AND $status){
			$mysqli->query("UPDATE ".$table_prefix."ping_check SET status='$status', ping_key='$ping_key', created='$time' WHERE id = $row[id]");
		}
	}
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

function gen_unlock_key($date,$key){
	$raw_key = sha1(base64_encode($date.$key));
	$raw_key = preg_replace("/[^0-9,.]/", "", $raw_key);
	return substr($raw_key,0,10);
}

function gen_ping_key($status,$date,$key) {
	return sha1(base64_encode($status.$date.$key));
}

//$check->free();
$mysqli->close();