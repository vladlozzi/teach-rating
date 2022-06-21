<?php
function digitEditor ($qid, $mid, $r) {
	global $conn, $editMode;
	$value = 0; $verStatus = 0; $teacherComment = "";
	mysqli_data_seek($r, 0); 
	while($rrow = mysqli_fetch_array($r)) {
		if($rrow['questionId'] == $qid) {
			$verStatus = $rrow['verifying_status_id'];
			$value = $rrow['digit']; $teacherComment = $rrow['teacherComment']; break;
		}
	}
	return ($editMode and $verStatus != 3) ? 
		'<input type="text" name="'.$qid.'" value="'.$value.'"><br>
		<textarea name="txa'.$qid.'" cols=36 rows=3>
		'.$teacherComment.'</textarea>' : "<center>".$value."</center>".
		(($value != 0) ? "<br><details>".$teacherComment."</details>" : "");
} // function digitEditor

function VerifyingStatus ($qid, $mid, $r) { // відображення статусу верифікації та можливості змінити
	global $conn, $editMode; // , $_POST;

	mysqli_data_seek($r, 0); $value = 0;
	while($rrow = mysqli_fetch_array($r)) {
		if($rrow['questionId'] == $qid) { $value = $rrow['verifying_status_id']; $cmt = $rrow['comment']; break; }
	}
	$checkboxChange = ""; // Кнопка "Змінити" - тільки для статусу "Підтверджено" або "Потребує підтвердження"
	if ($editMode) {
		// $_POST['chkChng'.$rrow['id']] = (isset($_POST['chkChng'.$rrow['id']])) ? $_POST['chkChng'.$rrow['id']] : "";
		if (($_SESSION['user_role'] == "ROLE_TEACHER") AND (($value == 2) or ($value == 3)))
			$checkboxChange = paramChekerWithoutWaitAutoSub('chkEdit'.$rrow['id'], $_POST['chkEdit'.$rrow['id']],
				'<span style="display: inline-block; color: darkblue; font-weight: bold; ">&nbsp;Змінити</span>');
	}
	switch ($value) {
		case 1: $ver_stat = "Новий".((!empty($cmt)) ? ".<br>".$cmt : ""); break;
		case 2: $ver_stat = "<span style=\"color: blue; font-weight: normal;\">
			Потребує підтвердження</span> ".$checkboxChange; break;
		case 3: $ver_stat = "<span style=\"color: green; font-weight: bold;\">
			Підтверджено</span> ".$checkboxChange; break;
		case 4: $ver_stat = "<span style=\"color: red;  font-weight: bold;\">
			Відхилено. ".$cmt." Виправте</span>"; break;
		default: $ver_stat = "";
	}
	return $ver_stat." (".$rrow['id'].") ";
} // function VerifyingStatus

function TesterById ($id) { // відображення підрозділу, який верифікує рейтинговий показник
	global $conn, $controwl_db;
	$Testers_query = "SELECT dekan_name FROM ".$controwl_db.".catalogDekan WHERE id = ".$id;
	$Testers_result = mysqli_query($conn, $Testers_query) or 
		die("Помилка сервера при запиті $Testers_query : ".mysqli_error($conn));
	$Testers_row = mysqli_fetch_array($Testers_result);
	$Tester = $Testers_row['dekan_name'];
	if ($Tester == "Тестовий декан") $Tester = "Директор інституту"; else $Tester = str_replace(" (рейтинг)", "", $Tester);
	return $Tester;
} // function TesterById

if (!defined("IN_ADMIN")) die;

$editMode = date("Y-m-d") < $date_of_close; // режим редагування

$stavka_query = "SELECT * FROM teachMatrix where id = ".$place;
$stavka_result = mysqli_query($conn, $stavka_query) or 
						die("Помилка сервера при запиті $stavka_query : ".mysqli_error($conn));
$stavka_row = mysqli_fetch_array($stavka_result);
$alpha = ($stavka_row['stavka'] < 1) ? 1 : $stavka_row['stavka']; // дільник для рейтингових показників
$qKPLastN = 1; // ID першого рейтингового показника, значення якого слід ділити на alpha
$qOVLastN = 91; // ID останнього рейтингового показника, значення якого слід ділити на alpha

// відділи контролю для рейтингових показників
$testers_query = " SELECT id, vidkontr_id FROM question ORDER BY id";
$testers_result = mysqli_query($conn, $testers_query) or die("Помилка сервера при запиті $testers_query : ".mysqli_error($conn));
$testers = array();
while ($testers_row = mysqli_fetch_array($testers_result)) $testers[$testers_row['id']] = $testers_row['vidkontr_id'];
// var_dump($testers);
//----------------------check if not exists add--------------------------------
$teachResults = mysqli_query($conn, "select * from teachResult where matrixId = '".$place."' order by questionId");
//echo ">".$place;
mysqli_data_seek($place_result,0);
while ($nexttest = mysqli_fetch_array($place_result)) {
	$found = 0;
	while ($test = mysqli_fetch_array($teachResults)) {
		if ($nexttest['id'] == $test['questionId']) {
			$found = 1; break;
		}
	}
	if ($found == 0) {
		mysqli_query($conn, "insert into teachResult 
			(`questionId`, `matrixId`,`verifying_status_id`,`tester_id`) 
			values ('".$nexttest['id']."', '".$place."','1','".$testers[$nexttest['id']]."')");
		$teachResults = mysqli_query($conn, "select * from teachResult where matrixId = '".$place."' order by questionId");
	}
	mysqli_data_seek($teachResults, 0);
}
mysqli_data_seek($place_result, 0);
//-----------------------------------------------------------------------------
$quest_rows = tableRowWrapper(
	tableHeaderWrapper("<center>№</center>").
	tableHeaderWrapper("<center>Рейтинговий показник</center>").
	tableHeaderWrapper("<center>Норматив, бали</center>").
	tableHeaderWrapper("<center>Оцінка, бали,<br>та&nbsp;пояснення</center>").
	tableHeaderWrapper("<center>Статус та ID запиту</center>").
	tableHeaderWrapper("<center>Примітка</center>").
	tableHeaderWrapper("<center>Підтверджує</center>"));
while($next = mysqli_fetch_array($place_result)) {
	if ($next['type'] == 1) //case global title
		$table_row = tableDigitWrapper("<center>".$next['name']."</center>", "colspan=\"7\"");
	else if ($next['type'] == 2)
		$table_row = 
			tableDigitWrapper((($next['number'] > 0) ? $next['number'] : ""), 
				"style=\"vertical-align: top; text-align: center;\" rowspan=\"".$next['magicField']."\""
			).
			tableDigitWrapper($next['name'], "colspan=\"4\"").
			tableDigitWrapper($next['comment'], "rowspan=\"".$next['magicField']."\"").
			tableDigitWrapper("", "colspan=\"2\"");
	else if ($next['type'] == 3)
		$table_row = tableDigitWrapper($next['name']).
			tableDigitWrapper("<center>".$next['maxDigit']."</center>").
			tableDigitWrapper(digitEditor($next['id'], $place, $teachResults)).
			tableDigitWrapper(VerifyingStatus($next['id'], $place, $teachResults, "")).
			tableDigitWrapper(TesterById($next['vidkontr_id']), "style=\"font-size: 75%;\"");
	else if ($next['type'] == 4)
		$table_row = 
			tableDigitWrapper($next['number'], "style=\"vertical-align: top; text-align: center;\"").
			tableDigitWrapper($next['name']).
			tableDigitWrapper("<center>".$next['maxDigit']."</center>").
			tableDigitWrapper(digitEditor($next['id'], $place, $teachResults)).
			tableDigitWrapper("").
			tableDigitWrapper(VerifyingStatus($next['id'], $place, $teachResults, "")).
			tableDigitWrapper(TesterById($next['vidkontr_id']), "style=\"font-size: 75%;\"");
	$quest_rows .= tableRowWrapper($table_row);
}
if(!$did) {
	mysqli_data_seek($teachResults, 0);
	$sumput = 0; $sumver = 0; $sumrate = 0; $discard_count = 0;
	while ($r = mysqli_fetch_array($teachResults)) {
		$sumput += $r['digit'];
		if ($r['verifying_status_id'] == 3) {
			$sumver += $r['digit'];
//			if (($r['questionId'] > $qKPLastN) and ($r['questionId'] <= $qOVLastN))
//				$sumrate += $r['digit'] / $alpha;
//			else 
				$sumrate += $r['digit'];
		}
		if ($r['verifying_status_id'] == 4) $discard_count++;
	}
	
	$quest_rows .= tableRowWrapper(
		tableDigitWrapper(bold("<center>Сума внесених балів</center>"), "colspan = \"3\"").
		tableDigitWrapper(bold("<center>".$sumput."</center>"), "colspan = \"1\"").
		tableDigitWrapper(" ", "colspan = \"3\"")).
		tableRowWrapper(tableDigitWrapper(bold("<center>Сума підтверджених балів</center>"), "colspan = \"3\"").
		tableDigitWrapper(bold("<center>".$sumver."</center>"), "colspan = \"1\"").
		tableDigitWrapper(" ", "colspan = \"3\"")).
		tableRowWrapper(tableDigitWrapper(bold("<center>Рейтингова сума балів*</center>"), "colspan = \"3\"").
		tableDigitWrapper(bold("<center>".round($sumrate,0)."</center>"), "colspan = \"1\"").
		tableDigitWrapper(" ", "colspan = \"3\""));
	if ($discard_count > 0) $quest_rows = 
		"<tr><td colspan=6><span style=\"display: inline; color: red;\">
		Увага!</span> Декілька ($discard_count) рейтингових показників, які Ви ввели, відхилено. ".
		((date("Y-m-d") < $date_of_close) ? 
		"Натисніть кнопку \"Для виправлень\" і введіть правильні значення. 
		Далі натисніть \"Зберегти і надіслати на підтвердження\".</td>
		<td><input type=\"submit\" name=\"correct\" value=\"Для виправлень\">" : "")."</td></tr>".
		$quest_rows;
	if ($_SESSION['user_role'] == 'ROLE_TEACHER')
		$quest_rows .= "<tr><td colspan=7><span style=\"display: inline; font-weight: normal; font-size: 85%;\">
			* Рейтингова сума складається з суми підтверджених балів за показниками 
			навчально-методичної, наукової, організаційно-адміністративної роботи<br>
			та підтверджених додаткових балів.</span></td></tr>";
}
$pageButtonAdd = $putData;
