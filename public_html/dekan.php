<?php
if(!defined("IN_ADMIN")) die;
require "chekers.php";
$fdepartment=$_SESSION['user_description']; $did = "";  $putData = "";
$_POST['verify_teachers_org'] = (isset($_POST['verify_teachers_org'])) ? $_POST['verify_teachers_org'] : "";
$_POST['teachers_black'] = (isset($_POST['teachers_black'])) ? $_POST['teachers_black'] : "";
$_POST['show_question'] = (isset($_POST['show_question'])) ? $_POST['show_question'] : "";
$_POST['put'] = (isset($_POST['put'])) ? $_POST['put'] : "";
$_POST['verify'] = (isset($_POST['verify'])) ? $_POST['verify'] : "";
$_GET['did'] = (isset($_GET['did'])) ? $_GET['did'] : "";

$verify_teachers_org = mysqli_real_escape_string($conn, trim($_POST['verify_teachers_org']));
$post_show_teachers_black = mysqli_real_escape_string($conn, trim($_POST['teachers_black']));
$show_question = mysqli_real_escape_string($conn, trim($_POST['show_question']));
$put = mysqli_real_escape_string($conn, trim($_POST['put']));
$verify = mysqli_real_escape_string($conn, trim($_POST['verify']));

$chks = paramChekerAutoSubInline("show_question", $show_question, 
		"Заповнити оцінки за організаційно-виховну роботу")."&nbsp; &nbsp; &nbsp;".
	paramChekerAutoSubInline("verify_teachers_org", $verify_teachers_org, 
		"Перевірити показники роботи викладачів")."<br>".
	paramChekerAutoSubInline("teachers_black", $post_show_teachers_black, 
		"Викладачі, які не ввійшли до рейтингу");
$page .= $chks."<br>";

$user_id = $_SESSION['user_id'];
$placeq = "
	select * from teachMatrix 
	where teacherId = $user_id and role = 4 and departmentId = $fdepartment
";
$results = mysqli_query($conn, $placeq) or die($placeq." : ".mysqli_error($conn));
if (mysqli_num_rows($results) <= 0) { // echo "fd=$fdepartment ... вставка";
	mysqli_query($conn, "insert into teachMatrix (`stavka`,`departmentId`,`teacherId`,`role`) 
							values ('1','".$fdepartment."','".$user_id."','4')") or die(mysqli_error($conn));
	$results = mysqli_query($conn, $placeq) or die($placeq." : ".mysqli_error($conn));
}
$place_row = mysqli_fetch_array($results);
$place = $place_row['id'];

switch (true) {
	case $verify_teachers_org :  require "verify_teachers_org.php"; break; 
	case $post_show_teachers_black :  require "teachers_black2_faculty.php"; break; 
	case $show_question : $table_rows = "";

		$place_query="SELECT question.* from question where role = 4 order by question.id";

		$place_query_update="SELECT question.*, teachResult.id as tid 
								from question left join teachResult 
								on question.id = teachResult.questionId 
								where teachResult.matrixId = '".$place."' and question.role = 4 
								order by question.id";
		$place_result = mysqli_query($conn, $place_query);
		$place_result_update = mysqli_query($conn, $place_query_update);
//---------------------------------------------------------------------------------------------------
		if($verify) { // збереження введених показників і встановлення статусу "Потребує підтвердження"
			while($qrow = mysqli_fetch_array($place_result_update)) { $qrow_id = $qrow['id'];
				$_POST[$qrow_id] = isset($_POST[$qrow_id]) ? $_POST[$qrow_id] : "";
				$_POST['txa'.$qrow_id] = (isset($_POST['txa'.$qrow_id])) ? $_POST['txa'.$qrow_id] : "";
				$update_query = "update teachResult set 
					digit = '".$_POST[$qrow_id]."', 
					teacherComment = \"".mysqli_escape_string($conn, $_POST["txa".$qrow_id])."\",
					verifying_status_id = 2 where id = '".$qrow['tid']."' and not (verifying_status_id = 3)";
//		echo "$update_query<br>"; 
				mysqli_query($conn, $update_query) or die(mysqli_error($conn)."<br>");
			}
		}
		$placeq = "select * from teachMatrix 
			where teacherId = '".$user_id."' and role = 4 and departmentId = '".$fdepartment."'";
		$results = mysqli_query($conn, $placeq);
		if (mysqli_num_rows($results) <= 0) {
			mysqli_query($conn, "insert into teachMatrix 
				(`stavka`,`departmentId`,`teacherId`,`role`) 
				values ('1','".$fdepartment."','".$user_id."','4'");
			$results = mysqli_query($conn, $placeq);
		}
		$place_row = mysqli_fetch_array($results);
		$place = $place_row['id'];
		require "record_selector.php";
		$putData = centerWrap("
			<!--
			<input type=\"submit\" name=\"put\" value=\"Зберегти\"> 
				&nbsp; &nbsp; &nbsp; &nbsp; -->
			<input style=\"color: navy;\" type=\"submit\" name=\"verify\" 
				value=\"Зберегти і надіслати на підтвердження\">
		");
		$page .= tableWrapper($quest_rows).$putData;
		break;
	default:
		$departRate_query = "
			SELECT a.*, a.suma / COUNT(*) AS rate 
			FROM departRating a, $controwl_db.catalogTeacher b 
			WHERE a.fid = $fdepartment AND 
				b.kaf_link = a.did AND b.role = 2 AND NOT (INSTR(b.teacher_surname, '~') OR INSTR(b.teacher_surname, '^')) 
			GROUP BY a.did 
			ORDER BY rate DESC
		";
//echo $departmentsq;
		$departmentsResult = mysqli_query($conn, $departRate_query) or die($departRate_query." ".mysqli_error($conn));;
//---------------------------------------------------------------------------------------------------
		$dindex = 1; $dnumbers = 0; $dsum = 0;
		$table_rows = tableRowWrapper(tableHeaderWrapper("№").tableHeaderWrapper("Кафедра").tableHeaderWrapper("Бали"));
		while($qrow = mysqli_fetch_array($departmentsResult)) { $dnumbers++;
			$dep_id = $qrow['did'];
			if ($_GET['did'] == $qrow['did']) {
				$link = "<a href=/>".$qrow['nazva_kaf']."</a>";
			} else {
				$link = "<a href=/?did=".$qrow['did'].">".$qrow['nazva_kaf']."</a>";
			}
			$table_rows .= tableRowWrapper(
				tableCenterWrapper($dindex).
				tableDigitWrapper($link).//"<a href=/?id=".$qrow['matrixId'].">".$qrow['name']."</a>").
				tableCenterWrapper(round($qrow['rate'], 0))
			);
			$dsum += round($qrow['rate'], 0);
			$dindex++;
			if ($_GET['did'] == $qrow['did']) { $dep_id = $qrow['did']; require "zavkaf.php"; }//echo $index;  
		}
		$page .= tableWrapper($table_rows, 'style="width: 65%; margin-left: auto; margin-right: auto;"');
		$dsq = "select sum(digit) as d from teachResult where matrixId = ".$place." and verifying_status_id=3";
		$dsr = mysqli_query($conn, $dsq) or die($dsq." : ".mysqli_error($conn)); // echo $dsq;
		$dsrow = mysqli_fetch_array($dsr);
		$deksum = $dsrow['d'];
		$page .= tableWrapper(tableRowWrapper(tableDigitWrapper("Сума балів:").tableHeaderWrapper(round($dsum, 0))).
			tableRowWrapper(tableDigitWrapper("Організаційно-виховна робота").tableHeaderWrapper($deksum)).
			tableRowWrapper(tableDigitWrapper("Рейтинг інституту:").tableHeaderWrapper(round($dsum/$dnumbers, 0)+$deksum)), 
			'style="width: 65%; margin-left: auto; margin-right: auto;"'
		)
		;
		break;
}
