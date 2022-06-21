<?php
if(!defined("IN_ADMIN")) die;
function logData($userId, $userRole, $changeId, $description) {
	global $conn;
	$logger_query = "
insert into progress_log(dateIns, ipAddress, userId, userRole, changeId, changeDescription) 
values('".date("Y-n-d G:i:s")."',
 '".$_SERVER['REMOTE_ADDR']."',
 '".$userId."',
 '".$userRole."',
 '".$changeId."',
 '".$description."')";
	$logger_sql = mysqli_query($conn, $logger_query) or die("try again later");
}

function logCompareData($previos, $current, $param, $paramId) {
	if($previos != $current) {
		logData($_SESSION['user_id'], $_SESSION['user_role'], $paramId, "change ".$param." - from - ".$previos." - to - ".$current." -");
	}
}
function logUpdateStudData($value) {
	logData($_SESSION['user_id'], $_SESSION['user_role'], "0", $value);
}
?>
