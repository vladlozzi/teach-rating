<?php
if(!defined("IN_ADMIN")) die;
require "chekers.php";

$editMode = date("Y-m-d") < $date_of_close; // режим редагування

// Мусиш пам'ятати !!!
// Цей скрипт, коли робить нову роботу для викладача, то вставляє всі питання.
// А якшо ти додав нові питання в question, мусиш додати сам безпосередньо в базу 
// для кожного викладача

$_POST['orcid'] = (isset($_POST['orcid'])) ? $_POST['orcid'] : "";
$_POST['scopusid'] = (isset($_POST['scopusid'])) ? $_POST['scopusid'] : "";
$_POST['place'] = (isset($_POST['place'])) ? $_POST['place'] : "";
$_POST['fakultet'] = (isset($_POST['fakultet'])) ? $_POST['fakultet'] : "";
$_POST['cPlace'] = (isset($_POST['cPlace'])) ? $_POST['cPlace'] : "";
$_POST['department'] = (isset($_POST['department'])) ? $_POST['department'] : "";
$_POST['add'] = (isset($_POST['add'])) ? $_POST['add'] : "";
$_POST['new'] = (isset($_POST['new'])) ? $_POST['new'] : "";
$_POST['put'] = (isset($_POST['put'])) ? $_POST['put'] : "";
$_POST['verify'] = (isset($_POST['verify'])) ? $_POST['verify'] : "";
$_POST['correct'] = (isset($_POST['correct'])) ? $_POST['correct'] : "";
$_POST['stavka'] = (isset($_POST['stavka'])) ? $_POST['stavka'] : "";
$_POST['shmarkli'] = (isset($_POST['shmarkli'])) ? $_POST['shmarkli'] : "";
$_POST['SaveIds'] = (isset($_POST['SaveIds'])) ? $_POST['SaveIds'] : "";
$_POST['stavkaedit'] = (isset($_POST['stavkaedit'])) ? $_POST['stavkaedit'] : "";
$did = (isset($_GET['did'])) ? $_GET['did'] : "";

$success = ""; $err = ""; $pageBottom = ""; $quest_rows = ""; $pageButtonAdd = "";
$orcid=mysqli_escape_string($conn, trim($_POST['orcid']));
$scopusid=mysqli_escape_string($conn, trim($_POST['scopusid']));
$place=mysqli_escape_string($conn, trim($_POST['place']));
$fakultet=mysqli_escape_string($conn, trim($_POST['fakultet']));
$post_cPlace=mysqli_escape_string($conn, trim($_POST['cPlace']));
$department=mysqli_escape_string($conn, trim($_POST['department']));
$add = mysqli_escape_string($conn, trim($_POST['add']));
$new = mysqli_escape_string($conn, trim($_POST['new']));
$put = mysqli_escape_string($conn, trim($_POST['put']));
$verify = mysqli_escape_string($conn, trim($_POST['verify']));
$correct = mysqli_escape_string($conn, trim($_POST['correct']));
$stavka = mysqli_escape_string($conn, trim($_POST['stavka']));
$stavkaedit = mysqli_escape_string($conn, trim($_POST['stavkaedit']));
$post_shmarkli=mysqli_escape_string($conn, trim($_POST['shmarkli']));
$saveids = mysqli_escape_string($conn, trim($_POST['SaveIds']));
//---------------------------------------------------------------------------------------------------
$qOADekanAsTeacherFirst = 83; // ID першого рейтингового показника директора інституту в ролі викладача
$qOADekanAsTeacherLast  = 91; // ID останнього рейтингового показника директора інституту в ролі викладача
$questionsCondition = "";
if ($_SESSION['user_isDekan'] != 1) 
	$questionsCondition = "and ((question.id < $qOADekanAsTeacherFirst) or (question.id > $qOADekanAsTeacherLast))";
$place_query="SELECT question.* from question where role = 2 $questionsCondition order by question.id";
$place_query_update="SELECT question.*, teachResult.id as tid 
	from question 
	left join teachResult on question.id = teachResult.questionId 
	where teachResult.matrixId = '".$place."' 
		and question.role = 2 $questionsCondition order by question.id";

$place_result = mysqli_query($conn, $place_query);
$place_result_update = mysqli_query($conn, $place_query_update) or die($place_query_update." : ".mysqli_error($conn));
while ($pru_row = mysqli_fetch_array($place_result_update)) {
	$id = $pru_row['id']; $_POST[$id] = (isset($_POST[$id])) ? $_POST[$id] : "";
}
// Обробляємо кнопки "Змінити" для показників, які вже підтверджені або потребують підтвердження.
// Змінюємо статус підтвердження на "Новий"
$place_result_update = mysqli_query($conn, $place_query_update) or die($place_query_update." : ".mysqli_error($conn));
while ($pru_row = mysqli_fetch_array($place_result_update)) {
	$tid = 'chkEdit'.$pru_row['tid'];
	$_POST[$tid] = (isset($_POST[$tid])) ? $_POST[$tid] : "";
	$change = mysqli_escape_string($conn, trim($_POST[$tid]));
	if ($change) { // echo "<script> alert('".$tid." = <".$change."> '); </script>";
		$update_query = "update teachResult set verifying_status_id = 1 where id = ".$pru_row['tid'];
//		echo $update_query."<br>";
		mysqli_query($conn, $update_query) or die(mysqli_error($conn)."<br>"); $_POST[$tid] = '';
	}
}

if ($saveids) { }

$place_result_update = mysqli_query($conn, $place_query_update) or die($place_query_update." : ".mysqli_error($conn));
//---------------------------------------------------------------------------------------------------
if ($correct) { // встановлення статусу "Новий" для виправлень після відхилення
	while($qrow = mysqli_fetch_array($place_result_update)) {
		$update_query = "update teachResult 
				    set verifying_status_id = 1
				    where id = '".$qrow['tid']."' and 
					verifying_status_id = 4";
//		echo "$update_query<br>"; 
		mysqli_query($conn, $update_query) or die(mysqli_error($conn)."<br>");
	}
}
if ($put) { // збереження посади, введених показників і встановлення статусу "Новий"
	while($qrow = mysqli_fetch_array($place_result_update)) { $qstr = $qrow['id'];
		$update_query = "update teachResult 
				    set digit = '".$_POST[$qstr]."', 
								teacherComment = \"".mysqli_escape_string($conn, $_POST["txa".$qstr])."\", 
								verifying_status_id = 1 
				    where id = '".$qrow['tid']."' and 
					 not (verifying_status_id = 2) and 
					 not (verifying_status_id = 3)";
//		echo "$update_query<br>";
		mysqli_query($conn, $update_query) or die(mysqli_error($conn)."<br>");
	}
// Збереження ORCID iD і Scopus Author's Id
	$orcid = mysqli_escape_string($conn, trim($_POST['orcid']));
	$scopusid = mysqli_escape_string($conn, trim($_POST['scopusid']));
	$update_ids_query = "update ".$controwl_db.".catalogTeacher 
		set orcid = \"".$orcid."\", scopus_id = \"".$scopusid."\"  where id = ".$user_id;
	mysqli_query($conn, $update_ids_query) or die($update_ids_query." : " .mysqli_error($conn)."<br>");

	if (empty($post_cPlace)) echo "<script>alert('Виберіть свою посаду і натисніть <<Зберегти>>!');</script>";
	else {
		$update_cplace_query = "update teachMatrix set place = '".$post_cPlace."' where id = ".$place;
		mysqli_query($conn, $update_cplace_query ) or die($update_cplace_query." : ".mysqli_error($conn));
	}
	if (empty($stavkaedit)) echo "<script>alert('Введіть свою частку ставки і натисніть <<Зберегти>>!');</script>";
	else {
		$update_stavka_query = "update teachMatrix set stavka = '".$stavkaedit."' where id = ".$place;
		mysqli_query($conn, $update_stavka_query ) or die($update_stavka_query." : ".mysqli_error($conn));
	}
}
if ($verify) { // збереження введених показників і встановлення статусу "Потребує підтвердження"
					// доповнено 2016.11.14 - там, де викладач увів значення, більше, ніж нуль
	while($qrow = mysqli_fetch_array($place_result_update)) { $qstr = $qrow['id'];
		$_POST['txa'.$qstr] = (isset($_POST['txa'.$qstr])) ? $_POST['txa'.$qstr] : "";
		$update_query = "update teachResult 
					set digit = '".$_POST[$qstr]."', 
							teacherComment = \"".mysqli_escape_string($conn, $_POST["txa".$qstr])."\", 
							verifying_status_id = 2 
					where id = '".$qrow['tid']."' and 
						not (verifying_status_id = 3)";
		// echo $qrow['maxDigit']." ".$_POST[$qrow['id']]." ||| "; // echo "$update_query<br>";
	   if (($qrow['maxDigit'] > 0) and ($_POST[$qrow['id']] > 0)
				or ($qrow['maxDigit'] < 0) and ($_POST[$qrow['id']] <= 0)) 
			mysqli_query($conn, $update_query) or die(mysqli_error($conn)."<br>");
		$update0_query = "update teachResult 
					set digit = 0, 
							teacherComment = \"".mysqli_escape_string($conn, $_POST["txa".$qstr])."\", 
							verifying_status_id = 1 
					where id = '".$qrow['tid']."' and 
						not (verifying_status_id = 3)";
		// echo $qrow['maxDigit']." ".$_POST[$qrow['id']]." ||| "; // echo "$update_query<br>";
	   if (($qrow['maxDigit'] > 0) and ($_POST[$qrow['id']] == 0)) 
			mysqli_query($conn, $update0_query) or die(mysqli_error($conn)."<br>");
	}
// Збереження ORCID iD і Scopus Author's Id
	$orcid = mysqli_escape_string($conn, trim($_POST['orcid']));
	$scopusid = mysqli_escape_string($conn, trim($_POST['scopusid']));
	$update_ids_query = "update ".$controwl_db.".catalogTeacher 
		set orcid = \"".$orcid."\", scopus_id = \"".$scopusid."\"  where id = ".$user_id;
	mysqli_query($conn, $update_ids_query) or die($update_ids_query." : " .mysqli_error($conn)."<br>");

	if (empty($post_cPlace)) echo "<script>alert('Виберіть свою посаду і натисніть <<Зберегти>>!');</script>";
	else {
		$update_cplace_query = "update teachMatrix set place = '".$post_cPlace."' where id = ".$place;
		mysqli_query($conn, $update_cplace_query ) or die($update_cplace_query." : ".mysqli_error($conn));
	}
	if (empty($stavkaedit)) echo "<script>alert('Введіть свою частку ставки і натисніть <<Зберегти>>!');</script>";
	else {
		$update_stavka_query = "update teachMatrix set stavka = '".$stavkaedit."' where id = ".$place;
		mysqli_query($conn, $update_stavka_query ) or die($update_stavka_query." : ".mysqli_error($conn));
	}
}

//custom button to add work place
$button_add_place = "<input type=\"submit\" name=\"add\" value=\"Додати місце роботи\">";
//button add result
$putData = ($editMode) ?
	centerWrap("<!-- <input type=\"submit\" name=\"put\" value=\"Зберегти\"> &nbsp; &nbsp; 
							&nbsp; &nbsp; -->
							<input style=\"color: navy;\" type=\"submit\" name=\"verify\" 
										value=\"Зберегти і надіслати на підтвердження\">") : "";
//--------------------------------put data ----------------------------------------------------------
if($new){
	if($department && $stavka) {
		$results = mysqli_query($conn, "select * from teachMatrix where teacherId = '".$user_id.
						"' and departmentId = '".$department."' and role = '2'");
		if (mysqli_num_rows($results) > 0) {
			$err = "<h1>вже додано</h1><br>";
		} else {
			mysqli_query($conn, "insert into teachMatrix (`stavka`,`departmentId`,`teacherId`,`role`) 
					values ('".str_replace(",", ".", $stavka)."','".$department."','".$user_id."','2')");
			if(!mysqli_error($conn)) {
				$success = "<h1>Дані успішно додано</h1>";
			}
		}
	} else if(!$stavka) {
		$err = "<h1>Введіть частку ставки</h1><br>";
	}
}
$sids_query = "SELECT orcid, scopus_id FROM ".$controwl_db.".catalogTeacher WHERE id = ".$user_id;
$sids_result = mysqli_query($conn, $sids_query) or die(mysqli_error($conn)."<br>");
$sids_row = mysqli_fetch_array($sids_result); $orcid = $sids_row['orcid']; $scopusid = $sids_row['scopus_id']; 
if ($editMode) {
	$table_rows = "
	<tr><td colspan=2 style=\"text-align: center; border: none;\">
	Введіть свій ORCID iD: <input type=\"text\" style=\"width: 250px;\" name=\"orcid\" value=\"".
		$orcid."\"><br>
	Введіть свій Scopus Author's Id: <input type=\"text\" style=\"width: 400px;\" name=\"scopusid\" value=\"".
	$scopusid."\"><br>
	".((0) ? 
	"<input type=\"submit\" name=\"SaveIds\" value=\"Зберегти\"></td></tr>" : "").
	"</td></tr>"
	; 
} else {
	$table_rows = "
	<tr><td colspan=2 style=\"text-align: center; border: none;\">
	Ваш ORCID iD: ".$orcid."<br>
	Ваш Scopus Author's Id: ".$scopusid."<br></td></tr>"
	;
}

$TeacherIsInMatrix_query = "select * from teachMatrix where teacherId = '".$user_id."' and role = '2'";
$TeacherIsInMatrix_result = mysqli_query($conn, $TeacherIsInMatrix_query) 
														or die("Помилка сервера при запиті TeacherIsInMatrix_query ".$TeacherIsInMatrix_query.
																		" : ".mysqli_error($conn));
$table_rows .= tableRowWrapper(tableDigitWrapper(
	((mysqli_num_rows($TeacherIsInMatrix_result) == 0) ? /* викладач не зареєструвався в рейтингу - дозволяємо додати */
		paramChekerWithoutWaitAutoSub("shmarkli", $post_shmarkli, "Додати місце роботи") : ""), "colspan=\"2\"")
);

if($success || $err) {
	$table_rows .= tableRowWrapper(
				tableDigitWrapper($success.$err, "colspan=\"2\"")
				);
}
//check if pressed button_add_place------------------------------------------------------------------
if($post_shmarkli) {
	$fakultet_query="SELECT * from cFakultet";
	$table_rows .= tableRowWrapper(
			tableDigitWrapper("Інститут").
			tableDigitWrapper(selectCommonChecker("fakultet", $fakultet_query, "id", $fakultet, "fakultet_name"))
			);
	if($fakultet) {
		$department_query = "SELECT * from cDepartment where fakultet_id ='".$fakultet."'";
		$table_rows .= tableRowWrapper(
				tableDigitWrapper("Кафедра").
				tableDigitWrapper(selectCommonChecker("department", $department_query, "id", $department, "nazva_kaf"))
				);
	}
	if($fakultet && $department) {
		$table_rows .= tableRowWrapper(
				tableDigitWrapper("Введіть частку ставки  
														(<span style=\"color: blue; display: inline\">обов'язково</span>: 
															1, 0.5, 0.25 тощо)").
				tableDigitWrapper("<input type=\"text\" name=\"stavka\" value=\"".$stavka."\">")
				);
		$pageBottom = "<input type=\"submit\" name=\"new\" value=\"Додати\">";
	}
} else {
//check if teacher has places then show selector else show add button--------------------------------
	$t_query="SELECT * from teachMatrixNames where teacherId = ".$user_id." AND role = 'ROLE_TEACHER'";
	$t_result = mysqli_query($conn, $t_query);
	$t_places = mysqli_num_rows($t_result);
	if($t_places) {
		$table_rows .= tableRowWrapper(
			"<td style=\"text-align: right; border: none;\">Виберіть місце роботи:</td>".
			tableDigitWrapper(selectTeacherChecker("place", $t_query, "id", $place, "nazva_kaf", "stavka"), "style=\"border: none;\""));
	}
}
$page .= tableWrapper($table_rows).$pageBottom;
//----------------------------------------------------------------------------------------------------
if($place && !$post_shmarkli) {
//------------------04.12.2018
	$place_in_teachMatrix_query = "SELECT place FROM teachMatrix WHERE id = ".$place;
	$place_in_teachMatrix_result = mysqli_query($conn, $place_in_teachMatrix_query) or 
		die($place_in_teachMatrix_query." : ".mysqli_error($conn));
	$place_in_teachMatrix_row = mysqli_fetch_array($place_in_teachMatrix_result);
	if (!empty($post_cPlace) and empty($place_in_teachMatrix_row['place'])
		or empty($post_cPlace) and !empty($place_in_teachMatrix_row['place'])) {
		if (empty($post_cPlace)) { $post_cPlace = $place_in_teachMatrix_row['place']; $_POST['cPlace'] = $post_cPlace; }
//		mysqli_query($conn, "update teachMatrix set place = '".$post_cPlace."' where id = ".$place);
	}
	$tmplace = "select place, stavka from teachMatrix where id = ".$place;
	$tmplaceresult = mysqli_query($conn, $tmplace);
	$tmplaceRow = mysqli_fetch_array($tmplaceresult);
	$tmplaceId = $tmplaceRow['place']; $tmplaceStavka = $tmplaceRow['stavka'];
	$q_place = "SELECT * from cPlace";
	$table_rows_cplace = tableRowWrapper(
		"<td style=\"text-align: right; border: none;\">".
		((date("Y-m-d") < $date_of_close) ? 
		"Виберіть посаду (<span style=\"color: blue; display: inline\">обовʼязково</span>)" : "Ваша посада").
		":</td>".
		tableDigitWrapper(selectCommonCheckerNoAutoSubmit("cPlace", $q_place, "id", $tmplaceId, "placeName"), "style=\"border: none;\"")
	).tableRowWrapper("
		<td style=\"text-align: right; border: none;\">".
		((date("Y-m-d") < $date_of_close) ? 
		"Введіть свою частку ставки (<span style=\"color: blue; display: inline\">обовʼязково</span>)" : "Ваша частка ставки").
		":</td>
		<td style=\"border: none;\"><input style=\"width: 50px;\" type=\"number\" name=\"stavkaedit\"
							min=\"0.01\" max=\"1.50\" step=\"0.01\" value=\"".$tmplaceStavka."\"></td>"
	);
//------------------04.12.2018
$page .= tableWrapper($table_rows_cplace);
//----------------------check if not exists add--------------------------------
//echo ">>".$place;
require "record_selector.php";
}
//----------------------------------------------------------------------------------------------------
$page .= tableWrapper($quest_rows).$pageButtonAdd;

