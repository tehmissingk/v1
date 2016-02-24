<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main unlock page.
 *
 * @package    additional
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once('lib.php');

// Try to prevent searching for sites that allow sign-up.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

$testsession = optional_param('testsession', 0, PARAM_INT); // test session works properly
$cancel      = optional_param('cancel', 0, PARAM_BOOL);      // redirect to frontpage, needed for loginhttps

if ($cancel) {
    redirect(new moodle_url('/'));
}

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$context = context_system::instance();
$PAGE->set_url("$CFG->httpswwwroot/login/unlock.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('unlock');

/// Initialize variables
$errormsg = '';
$errorcode = 0;
$today = date('Y-m-d');
$key = 'm201A!1';
$valid_unlock = $valid_ping = 0;

/// auth plugins may override these - SSO anyone?
$frm  = false;

/// Define variables used in page
$site = get_site();

$unlocksite = get_string("unlocksite");
$PAGE->navbar->add($unlocksite);

$frm = data_submitted();

if ($frm and isset($frm->unlockcode)) {
	$raw_key = sha1(base64_encode($today.$key));
	$raw_key = preg_replace("/[^0-9,.]/", "", $raw_key);
	$code = substr($raw_key,0,10);
	
	// ping test to lms.icarnegieasia.net
    $fsock = fsockopen("54.255.161.169", "80", $errno, $errstr, "1");
    if ( ! $fsock ){	// fail ping
		$errormsg = 'Failed to Connect to http://lms.icarnegieasia.net';
    } else {	// successful ping
        fclose($fsock);
		
		if($frm->unlockcode==$code){
			//$errormsg = 'Successful';
			
			$unlock_record = new stdClass();
			$unlock_record->date = $today;
			$unlock_record->unlock_key = $code;
			$unlock_record->created = time();
			$sql = "SELECT * FROM {unlock_check} WHERE date = :date LIMIT 1";
			$unlock_check = $DB->get_record_sql($sql,array('date'=>$today));
			if ($unlock_check) {
				$unlock_record->id = $unlock_check->id;
				$lastinsertid = $DB->update_record('unlock_check', $unlock_record, false);
			} else {
				$lastinsertid = $DB->insert_record('unlock_check', $unlock_record, false);
			}
			
			$ping_key = sha1(base64_encode('1'.$today.$key));
			$ping_record = new stdClass();
			$ping_record->status = '1';
			$ping_record->date = $today;
			$ping_record->ping_key = $ping_key;
			$ping_record->created = time();
			$sql = "SELECT * FROM {ping_check} WHERE date = :date LIMIT 1";
			$ping_check = $DB->get_record_sql($sql,array('date'=>$today));
			if ($ping_check) {
				$ping_record->id = $ping_check->id;
				$lastinsertid = $DB->update_record('ping_check', $ping_record, false);
			} else {
				$lastinsertid = $DB->insert_record('ping_check', $ping_record, false);
			}
		} else {
			$errormsg = 'Invalid Unlock Code';
		}
    }
}
$unlock_key_list = $DB->get_records_sql("SELECT * FROM {unlock_check} ORDER BY date DESC");
if ($unlock_key_list) {
	foreach ($unlock_key_list as $aidi => $value) {
		$unlock_key = gen_unlock_key($value->date,$key);
		if($unlock_key==$value->unlock_key){
			$unlock_date = $value->date;
			$valid_unlock = 1;
			break;
		}
	}
	if ($valid_unlock) {
		$valid_ping = 1;
		$now = time(); // or your date as well
		$unlock_date = strtotime($unlock_date);
		$datediff = $now - $unlock_date;
		$datediff = floor($datediff/(60*60*24));
		
		if($datediff >= 7){
			$seven_day = "'".date('Y-m-d', strtotime('-1 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-2 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-3 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-4 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-5 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-6 day', strtotime($date)))."',";
			$seven_day .= "'".date('Y-m-d', strtotime('-7 day', strtotime($date)))."'";
			
			$ping_key_list = $DB->get_records_sql("SELECT * FROM {ping_check} WHERE date IN ($seven_day) AND status = 1 ORDER BY date DESC");
			if ($ping_key_list) {
				$total_fake = $total_data = 0;
				foreach ($ping_key_list as $aidi => $value) {
					$total_data++;
					if ($value->ping_key != gen_ping_key($value->status,$value->date,$key)) {
						$total_fake++;
					}
				}
				if ($total_data==$total_fake) {	// all ping is fake
					$valid_ping = 0;
				}
			} else {	// no ping recorded ever
				$valid_ping = 0;
			}
		} else if ($datediff >= 0 AND $datediff < 7) {	// still below 7 days, should proceed
			$valid_ping = 1;
		}
	}
}

$PAGE->set_title("$site->fullname: $unlocksite");
$PAGE->set_heading("$site->fullname");

echo $OUTPUT->header();

if (isloggedin() and !isguestuser()) {
    // prevent logging when already logged in, we do not want them to relogin by accident because sesskey would be changed
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url($CFG->httpswwwroot.'/login/logout.php', array('sesskey'=>sesskey(),'loginpage'=>1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url($CFG->httpswwwroot.'/login/unlock.php', array('cancel'=>1)), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('alreadyloggedin', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
} else {
    include("index_form.html");
    if ($errormsg) {
        $PAGE->requires->js_init_call('M.util.focus_login_error', null, true);
    } else if (!empty($CFG->loginpageautofocus)) {
        //focus username or password
        $PAGE->requires->js_init_call('M.util.focus_login_form', null, true);
    }
}

echo $OUTPUT->footer();


function gen_unlock_key($date,$key){
	$raw_key = sha1(base64_encode($date.$key));
	$raw_key = preg_replace("/[^0-9,.]/", "", $raw_key);
	return substr($raw_key,0,10);
}

function gen_ping_key($status,$date,$key) {
	return sha1(base64_encode($status.$date.$key));
}