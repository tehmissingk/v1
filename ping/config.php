<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cmasialms54';
// default table prefix for moodle, do not change
$table_prefix = 'mdl_';
$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	die();
}
echo 'connect to database: success<br />';