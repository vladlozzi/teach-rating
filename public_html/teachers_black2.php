<?php
if(!defined("IN_ADMIN")) die;
$subpage0 = "";
 
$NoTeachers = "&nbsp;&nbsp;&nbsp;- таких викладачів немає";
$subpage1 = ""; $subpage2 = ""; $subpage3 = "";
$TeachNotRegisteredQuery = "SELECT * FROM teachersAreAbsentInPuts WHERE point = 0";
$TeachNotRegisteredQuery_result = mysqli_query($conn, $TeachNotRegisteredQuery) or
	die("Помилка сервера при запиті<br>".$TeachNotRegisteredQuery." : ".mysqli_error($conn));
$icnt = 0; $icnt_must_reg = 0; 
while ($query_row = mysqli_fetch_array($TeachNotRegisteredQuery_result)) { 
	$icnt_must_reg++;
 	$subpage1 .= "&nbsp;&nbsp;&nbsp;$icnt_must_reg. ".$query_row['teacher_surname']." ".
					$query_row['teacher_name']." ".$query_row['teacher_pobatkovi']." - ".
					$query_row['nazva_kaf']."<br>";
}

$TeachProblemQuery = "SELECT * FROM teachersHaveProblemsInRating";
$TeachProblemQuery_result = mysqli_query($conn, $TeachProblemQuery) or
	die("Помилка сервера при запиті<br>".$TeachProblemQuery." : ".mysqli_error($conn));
$icnt_have_problem = 0; 
while ($query_row = mysqli_fetch_array($TeachProblemQuery_result)) { 
	$icnt_have_problem++;
 	$subpage2 .= "&nbsp;&nbsp;&nbsp;$icnt_have_problem. ".$query_row['teacher_surname']." ".
					$query_row['teacher_name']." ".$query_row['teacher_pobatkovi']." - ".
					$query_row['nazva_kaf']."<br>";
}

$TeachRestQuery = "SELECT * FROM teachersAreAbsentInPuts WHERE point = 1";
$TeachRestQuery_result = mysqli_query($conn, $TeachRestQuery) or
	die("Помилка сервера при запиті<br>".$TeachRestQuery." : ".mysqli_error($conn));
$icntRest = 0;
while ($query_row = mysqli_fetch_array($TeachRestQuery_result)) {
	$icntRest++;
	$subpage3 .= "&nbsp;&nbsp;&nbsp;$icntRest. ".$query_row['teacher_surname']." ".
					$query_row['teacher_name']." ".$query_row['teacher_pobatkovi']." - ".
					$query_row['nazva_kaf']."<br>";
}

$icnt += $icnt_must_reg;
$teachers = teachers($icnt);
$teachers_must_reg = teachers($icnt_must_reg);
// $teachers .= ($teachers == "викладачі") ? " з" : " із";
$tcnt = $icnt_must_reg + $icnt_have_problem + $icntRest;

if (empty($subpage1)) $subpage1 = $NoTeachers;
if (empty($subpage2)) $subpage2 = $NoTeachers;
if (empty($subpage3)) $subpage3 = $NoTeachers;
$subpage = "<h3>Сумарна кількість викладачів, які не ввійшли до рейтингу: $tcnt, з них:</h3>
	<strong><em><h3>- не зареєстрували свої місце роботи, ставки, посаду в рейтинговій системі:</h3></em></strong>
	$subpage1
	<strong><em><h3>- не закінчили введення даних, не надіслали на підтвердження або не пройшли перевірку:</h3></em></strong>
	$subpage2
	<strong><em><h3>- працюють погодинно, тимчасово,
	перебувають у декретних відпустках або звільнені протягом звітного року
	і в рейтингу участі не беруть:</h3></em></strong>
	$subpage3<p style=\"margin-top: 5px;\"><strong>Умовні позначення:</strong> \"*\" - працює за сумісництвом, \"~\" - працює погодинно, 
	\"^\" - у \"декреті\", \"_\" - звільнено, але залишено в рейтингу.</p>";
$page .= $subpage;
?>
