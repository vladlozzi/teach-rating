<?php
if(!defined("IN_ADMIN")) die; // if manual used - drop script
//--------------------------------------------------------------------
$role_name = "";
switch ($_SESSION['user_role'])
{
	case 'ROLE_STUDENT' : $role_name = "студент"; break;
	case 'ROLE_TEACHER' : $role_name = "викладач"; break;
	case 'ROLE_ZAVKAF'  : $role_name = "завідувач кафедри"; break;
	case 'ROLE_DEKAN'   : $role_name = "директор інституту"; break;
	case 'ROLE_RECTOR'  : $role_name = "(про)ректор"; break;
	case 'ROLE_TEST_DEP': $role_name = ""; break;
	case 'ROLE_ADMIN'   : $role_name = "адміністратор"; break;
	case 'ROLE_DEKANAT' : $role_name = "працівник деканату"; break;
}	
$user_name = $role_name." ".$_SESSION['user_fullname'];
//-------------------information message--------------------------
$page .= str_replace("Kiev","Kyiv",date("Y-m-d H:i:s (e P)"))." &nbsp; Ви увійшли як ".newLineAfter(bold($user_name));
?>
