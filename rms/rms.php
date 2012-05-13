<?php
if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) 
	die('I fart in your general direction. Your mother was a 
hamster and your father smelt of elderberries.  Now go away, or I 
will be forced to taunt you a second time.');

require '/usr/local/www/hackthissite.org/rms.config.php';

// Check if server id exists
if (empty($ips[$_GET['serverId']])) die('0serverid');

$ipData = explode(':', $_GET['ip']);
// Check if ip is covered by server id
if (!in_array($ipData[0], $ips[$_GET['serverId']])) die('0ip');
//Check if mission id is covered by server id
if (!in_array($_GET['missionId'], $missions[$_GET['serverId']])) die('0missionid');
// Check if user reported has actually requested to do this mission
if (!apc_exists('rms_' . $_GET['userId'])) die('0useridne');

echo '1';

// Do DB stuff here
require "./../config.php";
$mysql = new mysqli($dbauth['host'], $dbauth['user'],
	$dbauth['pass'], $dbauth['name'], $dbauth['port']);
$data = $mysql->query("SELECT remote FROM missions_completed WHERE 
user_id = " . apc_fetch('rms_' . $_GET['userId']));
$row = $data->fetch_assoc();
$row = explode(',', $row['remote']);
array_push($row, $links[$_GET['missionId']]);
$row = array_filter(array_unique($row));
asort($row);
$row = implode(',', $row);


$mysql->query("UPDATE missions_completed SET remote = '" . $row . "' 
WHERE user_id = " . apc_fetch('rms_' . $_GET['userId']));
?>
