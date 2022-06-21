<?php
if(!defined("IN_ADMIN")) die;
$dep_id = isset($dep_id) ? $dep_id : '';
if (!empty($dep_id)) $_POST['radSortSelect'] = 'Alphabet';
$_POST['radSortSelect'] = isset($_POST['radSortSelect']) ? $_POST['radSortSelect'] : 'Id';
$Where = (!empty($dep_id)) ? 'WHERE role="ROLE_TEACHER" AND departmentId = '.$dep_id.' ' : '';
$OrderBy = '';

if (empty($dep_id)) $page .= "
<p style=\"text-align: center; color: blue; margin-top: 0.5em; margin-bottom: 0.2em; \">
Сортувати: &nbsp; 
<input type=\"radio\" name=\"radSortSelect\" value=\"Id\" onclick=\"submit()\" ". 
	(($_POST['radSortSelect'] == "Id") ? "checked" : ""). ">у порядку заповнення &nbsp; &nbsp; 
<input type=\"radio\" name=\"radSortSelect\" value=\"Alphabet\" onclick=\"submit()\" ". 
	(($_POST['radSortSelect'] == "Alphabet") ? "checked" : ""). ">в алфавітному порядку
</p>"; else $page .= "<p style=\"text-align: center; font-size: 120%; margin-top: 0.5em; margin-bottom: 0.2em; \">
										Оперативна інформація про введені викладачами дані</p>";
switch ($_POST['radSortSelect']) {
	case 'Id': $OrderBy = 'ORDER BY a.id DESC'; break;
	case 'Alphabet': $OrderBy = 'ORDER BY a.fakultet, a.nazva_kaf, a.name'; break;
}
$Teachers_query = "
SELECT a.*, b.placeName FROM teachMatrixNames a 
LEFT JOIN cPlace b ON (b.id = a.place) 
".$Where.$OrderBy;
$Teachers_result = mysqli_query($conn, $Teachers_query) or die($Teachers_query." : ".mysqli_error($conn));
$tnumb = ($OrderBy == 'ORDER BY a.id DESC') ? mysqli_num_rows($Teachers_result) + 1 : 0;
$ztable_rows = tableRowWrapper(
             tableHeaderWrapper("№").tableHeaderWrapper("Id").
             ((empty($dep_id)) ? tableHeaderWrapper("Інститут") : "").
             ((empty($dep_id)) ? tableHeaderWrapper("id/ Кафедра") : "").
             tableHeaderWrapper("id/ Прізвище, ім'я та по батькові").
             tableHeaderWrapper("Посада").
             tableHeaderWrapper("Частка<br>ставки").
             tableHeaderWrapper("Бали,&nbsp;які<br>введено").
             tableHeaderWrapper("Очікують<br>підтвердж.").
             tableHeaderWrapper("Відхи-<br>лено").
             tableHeaderWrapper("Підтвер-<br>джено")
             ); $Sals = 0; $InBals = 0; $WaitBals = 0; $FailBals = 0; $PassBals = 0;
while ($Teachers_row = mysqli_fetch_array($Teachers_result)) {
	if ($OrderBy == 'ORDER BY a.id DESC') $tnumb--; else $tnumb++;
	$Bals_query = "SELECT SUM(digit) AS bals FROM teachResult WHERE matrixId = ".$Teachers_row['id'];
	$Bals_result = mysqli_query($conn, $Bals_query) or die($Bals_query." : ".mysqli_error($conn));
	$Bals_row = mysqli_fetch_array($Bals_result);
	$Wait_query = "SELECT SUM(digit) AS bals FROM teachResult 
								 WHERE verifying_status_id = 2 AND matrixId = ".$Teachers_row['id'];
	$Wait_result = mysqli_query($conn, $Wait_query) or die($Wait_query." : ".mysqli_error($conn));
	$Wait_row = mysqli_fetch_array($Wait_result);
	$Fail_query = "SELECT SUM(digit) AS bals FROM teachResult 
								 WHERE verifying_status_id = 4 AND matrixId = ".$Teachers_row['id'];
	$Fail_result = mysqli_query($conn, $Fail_query) or die($Fail_query." : ".mysqli_error($conn));
	$Fail_row = mysqli_fetch_array($Fail_result);
	$Pass_query = "SELECT SUM(digit) AS bals FROM teachResult 
								 WHERE verifying_status_id = 3 AND matrixId = ".$Teachers_row['id'];
	$Pass_result = mysqli_query($conn, $Pass_query) or die($Pass_query." : ".mysqli_error($conn));
	$Pass_row = mysqli_fetch_array($Pass_result);
	$ztable_rows .= tableRowWrapper(
		tableDigitWrapper(centerWrap($tnumb)).tableDigitWrapper(centerWrap($Teachers_row['id'])).
		((empty($dep_id)) ? tableDigitWrapper(centerWrap($Teachers_row['fakultet'])) : "").
		((empty($dep_id)) ? tableDigitWrapper($Teachers_row['departmentId']."/ ".$Teachers_row['nazva_kaf']) : "").
		tableDigitWrapper($Teachers_row['teacherId']."/ ".$Teachers_row['name']).
		tableDigitWrapper((($Teachers_row['role'] == "ROLE_DEKAN") ? "Директор" : 
															(($Teachers_row['placeName'] != NULL) ? $Teachers_row['placeName'] :
													 		"<span style=\"color: red; font-size: 85%; \">Не&nbsp;введена</span>"))).
		tableDigitWrapper("&nbsp; &nbsp; ".$Teachers_row['stavka']).
		tableDigitWrapper((($Bals_row['bals'] == "") ? "<span style=\"color: red; font-size: 85%; \">Не&nbsp;введено</span>" :
											"<span style=\"color: blue;\">&nbsp; &nbsp; ".$Bals_row['bals']."</span>")).
		tableDigitWrapper((($Wait_row['bals'] == "" and $Bals_row['bals'] > $Fail_row['bals'] + $Pass_row['bals']) ? 
												"<span style=\"color: red; font-size: 85%; \">Не&nbsp;надіслано</span>" :
											"<span style=\"color: blue;\">&nbsp; &nbsp; ".$Wait_row['bals']."</span>")).
		tableDigitWrapper("<span style=\"color: red;\">&nbsp; &nbsp; ".$Fail_row['bals']."</span>").
		tableDigitWrapper("<span style=\"color: darkgreen;\">&nbsp; &nbsp; ".$Pass_row['bals']."</span>")
	);
	$Sals += $Teachers_row['stavka']; $InBals += $Bals_row['bals']; $WaitBals += $Wait_row['bals'];
	$FailBals += $Fail_row['bals']; $PassBals += $Pass_row['bals'];
}
switch ($_SESSION['user_role']) {
	case "ROLE_ZAVKAF" : $colspan = 4; break;
	default: $colspan = 6;
}
$ztable_rows .= tableRowWrapper(
	tableDigitWrapper(bold("Сума: &nbsp;"), "style=\"text-align: right;\" colspan=".$colspan)
	.tableDigitWrapper(bold($Sals)).tableDigitWrapper(bold($InBals)).tableDigitWrapper(bold($WaitBals))
	.tableDigitWrapper(bold($FailBals)).tableDigitWrapper(bold($PassBals))
);
$page .= tableWrapper($ztable_rows);
