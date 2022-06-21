<?php
if(!defined("IN_ADMIN")) die;
require "chekers.php";
$_POST['radViewSelect'] = isset($_POST['radViewSelect']) ? $_POST['radViewSelect'] : "Faculties";
$page .= "
<p style=\"text-align: center; color: blue; margin-top: 0.5em; margin-bottom: 0.2em; \">
Показати: &nbsp; 
<input type=\"radio\" name=\"radViewSelect\" value=\"Faculties\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "Faculties") ? "checked" : ""). ">Рейтинг інститутів &nbsp; &nbsp; 
<input type=\"radio\" name=\"radViewSelect\" value=\"Departments\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "Departments") ? "checked" : ""). ">Рейтинг кафедр &nbsp; &nbsp; 
<input type=\"radio\" name=\"radViewSelect\" value=\"Teachers\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "Teachers") ? "checked" : ""). ">Рейтинг викладачів<br>
<input type=\"radio\" name=\"radViewSelect\" value=\"BlackList\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "BlackList") ? "checked" : ""). ">Викладачів, які не ввійшли до рейтингу &nbsp; &nbsp;
<input type=\"radio\" name=\"radViewSelect\" value=\"Testers\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "Testers") ? "checked" : ""). ">Стан перевірки рейтингу &nbsp; &nbsp;
	".
(($_SESSION['user_id'] == 79 AND $_SESSION['user_role'] == "ROLE_VICERECTOR") ? "<br>
<input type=\"radio\" name=\"radViewSelect\" value=\"Dekans\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "Dekans") ? "checked" : ""). ">Підтвердити показники директорів інститутів / викладачів" : "")."<br>
<input type=\"radio\" name=\"radViewSelect\" value=\"WhiteList\" onclick=\"submit()\" ". 
	(($_POST['radViewSelect'] == "WhiteList") ? "checked" : ""). ">Викладачів, які зареєструвалися в рейтингу
</p>";

$_GET['allteachrate'] = isset($_GET['allteachrate']) ? $_GET['allteachrate'] : "";
$_GET['id'] = isset($_GET['id']) ? $_GET['id'] : "";
$get_allteachrate = mysqli_real_escape_string($conn, trim($_GET['allteachrate']));
$get_id = mysqli_real_escape_string($conn, trim($_GET['id']));
if ($get_allteachrate == "1") $_POST['all_teachers'] = "on";
$_GET['fid'] = isset($_GET['fid']) ? $_GET['fid'] : "";
$_GET['did'] = isset($_GET['did']) ? $_GET['did'] : "";
$getfid = mysqli_real_escape_string($conn, trim($_GET['fid']));
$did = ""; $putData = "";

switch ($_POST['radViewSelect']) {
	case "Teachers" : require "all_rate.php"; break;
	case "Departments" : require "all_departs_rate.php"; break;
	case "Dekans" : require "verify_dekans.php"; break;
	case "BlackList" : require "teachers_black2.php"; break;
	case "WhiteList" : require "teachers_registered.php"; break;
	case "Testers" : require "testers_black.php"; break;
	case "Faculties" :
		$lq = "SELECT sum, count, frate, rate, fid, fname FROM fakultetRateWithDekan";
		$lq = "SELECT * FROM facultiesTotalRate";
		$lqres = mysqli_query($conn, $lq) or die($lq." : ".mysqli_error($conn));
		$table_rows = "";
		$table_rows .= tableRowWrapper( tableHeaderWrapper(bold(centerWrap("Інститут")), "colspan=\"4\"")
			.tableHeaderWrapper(bold(centerWrap("Кількість<br>кафедр")))
			.tableHeaderWrapper(bold(centerWrap("Сума балів<br>кафедр")))
			.tableHeaderWrapper(bold(centerWrap("Сума балів<br>директора")))
			.tableHeaderWrapper(bold(centerWrap("Рейтингова<br>сума балів"))) );
		$departs = 0; $departs_b = 0; $dekans_b = 0;
		while($lqrow = mysqli_fetch_array($lqres)) {
			$fid = $lqrow['id'];
			if($getfid == $fid) {
				$link = "<a href=/ onclick=\"document.getElementById('blink').hidden = false\">".$lqrow['fname']." </a>";
			} else {
				$link = "<a href=/?fid=".$fid." onclick=\"document.getElementById('blink').hidden = false\" >".$lqrow['fname']."</a>";
			}
			$table_rows .= tableRowWrapper(tableDigitWrapper($link, "colspan=\"4\"").
				tableDigitWrapper(centerWrap((($lqrow['departsCount'] > 1) ? $lqrow['departsCount'] : "-"))).
				tableDigitWrapper(centerWrap((($lqrow['departsCount'] > 1) ? $lqrow['departsSum'] : "-"))).
				tableDigitWrapper(centerWrap(($lqrow['dekanSum'] == NULL) ? "Бали не введено" : $lqrow['dekanSum'])).
				tableDigitWrapper(centerWrap($lqrow['facultyTotalRate'])));
			if ($lqrow['departsCount'] > 1) $departs += $lqrow['departsCount'];
			$departs_b += $lqrow['departsSum']; $dekans_b += $lqrow['dekanSum'];
			$departmentsq = "
				SELECT a.*, a.suma / COUNT(*) AS rate 
				FROM departRating a, $controwl_db.catalogTeacher b 
				WHERE a.fid = $fid AND 
					b.kaf_link = a.did AND b.role = 2 AND NOT (INSTR(b.teacher_surname, '~') OR INSTR(b.teacher_surname, '^')) 
				GROUP BY a.did 
				ORDER BY rate DESC
			";
			$departmentsResult = mysqli_query($conn, $departmentsq);
			$dindex = 1;
			if($fid == $getfid) { $dsum = 0; $dnumbers = 0;
				while($qrow = mysqli_fetch_array($departmentsResult)) { $dnumbers++;
					$link = "<a href=/?fid=".$_GET['fid'];
					if($_GET['did'] != $qrow['did']) {
						$link .= "&did=".$qrow['did'];
					}
					$link .= ">".$qrow['nazva_kaf']."</a>";
					$table_rows .= tableRowWrapper(
						tableDigitWrapper($dindex).
						tableDigitWrapper($link, "colspan=\"4\"").
						tableDigitWrapper(round($qrow['rate'], 0)) );
					$dsum += $qrow['rate']; $dindex++;
					if ($_GET['did'] == $qrow['did']) {
						$dep_id = $qrow['did']; require "zavkaf.php";
					}//echo $index;
				}
			}
		}
		$table_rows .= tableRowWrapper( tableHeaderWrapper(bold(centerWrap("Разом")), "colspan=\"4\"")
			.tableHeaderWrapper(bold(centerWrap($departs)))
			.tableHeaderWrapper(bold(centerWrap($departs_b)))
			.tableHeaderWrapper(bold(centerWrap($dekans_b)))
			.tableHeaderWrapper(bold(centerWrap("---"))) );
		$page .= '
			<p style="font-size: 125%; text-align: center;">
			Рейтинг інститутів
			</p>
		' . tableWrapper($table_rows).'
			<p style="font-size: 90%;">
				<em>Для визначення рейтингу враховуються бали, підтверджені уповноваженими особами.</em>
			</p>
		';
		break;
	default: require "testers_black.php";
}