<?php
if(!defined("IN_ADMIN")) die;
// echo "test_depart OK";
require "chekers.php";
$questions = array(); // назви рейтингових показників
$normbals = array(); // нормативні бали рейтингових показників
$Question_query = "SELECT * FROM question ORDER BY id";
$Question_result = mysqli_query($conn, $Question_query) or 
			    die("Помилка сервера при запиті $Question_query : ".mysqli_error($conn));
while ($Question_row = mysqli_fetch_array($Question_result)) {
    $questions[$Question_row['id']] = $Question_row['name']; 
    $normbals[$Question_row['id']] = $Question_row['maxDigit'];
}

$page .= "<br>Підтвердіть рейтингові показники.
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp;
	<input type=\"button\" name=\"refresh\" value=\"Оновити список\"
		onclick=\"history.go(0)\">
	<br>Кількість запитів на підтвердження: ";

$TeachersCount_query = "SELECT COUNT(DISTINCT(a.matrixId)) AS t_cnt 
			FROM teachResult a, teachMatrixNames b, question c 
			WHERE a.verifying_status_id = 2 and a.tester_id = $user_id and 
				b.id = a.matrixId and c.magicField = '' and c.id = a.questionId";
$TeachersCount_result = mysqli_query($conn, $TeachersCount_query) or 
			    die("Помилка сервера при запиті $TeachersCount_query : ".mysqli_error($conn));
$TeachersCount_row = mysqli_fetch_array($TeachersCount_result);
$page .= bold($TeachersCount_row['t_cnt'])."<br>";
if ($TeachersCount_row['t_cnt'] == 0) return;
$teachers_sort_order = "b.nazva_kaf, b.name";
$teachers_sort_order = "b.name";
$tr = "<tr><td style=\"text-align: right;\">Виберіть викладача:</td>";
$MatrixSelect_query =  "SELECT DISTINCT a.matrixId, 
											CONCAT(b.name,\" - \",b.nazva_kaf) as teacher
								FROM teachResult a, teachMatrixNames b, question c
								WHERE b.id = a.matrixId and 
										c.magicField = '' and c.id = a.questionId and 
										a.verifying_status_id = 2 and a.tester_id = $user_id
								ORDER BY ".$teachers_sort_order;
$tmmatrixId = (isset($_POST['teacher'])) ? $_POST['teacher'] : "";

$tr .= "<td>".selectCommonChecker("teacher", $MatrixSelect_query, 
									"matrixId", $tmmatrixId, "teacher")."</td></tr>";
$page .= tableWrapper($tr);

if (!empty($_POST['teacher'])) { // викладача вибрано
//	$page .= $_POST['teacher']; 
	// Якщо користувач - науково-технічна бібіліотека, 
	// то взяти для викладача його ORCID iD і Scopus Author's Id
	if ($user_id == 54) {
		$Teacher_query = "
			SELECT a.orcid, a.scopus_id 
			FROM ".$controwl_db.".catalogTeacher a, teachMatrix b
			WHERE a.id = b.teacherId AND b.id = ".$_POST['teacher']
		;
		$Teacher_result = mysqli_query($conn, $Teacher_query) or 
			die("Помилка сервера при запиті $Teacher_query : ".mysqli_error($conn));
		$Teacher_row = mysqli_fetch_array($Teacher_result);
		$tr = "<tr><td>ORCID iD: <a href=\"".$Teacher_row['orcid']."\" 
																target=\"_blank\">".$Teacher_row['orcid']."</a></td></tr>";
		$tr .= "<tr><td>Scopus Author's Id: <a href=\"".$Teacher_row['scopus_id']."\" 
																					target=\"_blank\">".$Teacher_row['scopus_id']."</a></td></tr>";
		$page .= tableWrapper($tr);
	}
	$Tester_query =  "(
											SELECT * FROM teachResult 
											WHERE tester_id = $user_id AND matrixId = '".$_POST['teacher']."' AND verifying_status_id = 2
										) 
										UNION 
										(
											SELECT 
												0 AS id, '".$_POST['teacher']."' AS matrixId, id AS questionId, 0 AS digit, 
												'' AS teacherComment, 0 AS verifying_status_id, $user_id AS tester_id, '' AS comment 
											FROM question 
											WHERE vidkontr_id = $user_id AND type = 2
										) 
										ORDER BY questionId";

//	$page .= bold($Tester_query)."<br>";
	$Tester_result = mysqli_query($conn, $Tester_query) or 
			    die("Помилка сервера при запиті $Tester_query : ".mysqli_error($conn));
	$question_ids = array(); $iq = 0;
	while ($Tester_row = mysqli_fetch_array($Tester_result)) {
	   $qi = ($normbals[$Tester_row['questionId']] != 0) ? $Tester_row['questionId'] : "";
	    $question_ids[$iq] = $qi; $iq++;
	}
	if (!empty($_POST['save'])) { // var_dump($question_ids);
	   foreach ($question_ids as $qi) {
			if (!empty($qi) and !empty($_POST['radioCDC'.$qi])) {
				$update_tR_query = "
					UPDATE teachResult 
					SET verifying_status_id = \"".$_POST['radioCDC'.$qi]."\", 
							comment = \"".$_POST['txa'.$qi]."\" 
					WHERE tester_id = $user_id AND 
								matrixId = ".$_POST['teacher']." AND 
								questionId = $qi";
				// echo "<br>".$update_tR_query;
				$update_tR_result = mysqli_query($conn, $update_tR_query) or 
									    die("Помилка сервера при запиті $update_tR_query : ".
										mysqli_error($conn));
			}
	   }
	}
   $th = "<tr>	<th>ID</th><th>Рейтинговий показник</th>
					<th style=\"font-size: 90%\">Норматив,<br>бали</th>
					<th style=\"font-size: 90%\">Бали,&nbsp;введені викладачем, та&nbsp;їх&nbsp;пояснення</th><th style=\"width: 100px;\">Рішення</th>
					<th style=\"width: 150px;\">Чому відхилили</th>		</tr>";
//	$Tester_query =  "SELECT *	FROM teachResult a 
//										WHERE a.tester_id = $user_id and a.matrixId = '".$_POST['teacher'].
//									"'	ORDER BY a.questionId";
//	$page .= bold($Tester_query)."<br>";
	$Tester_result1 = mysqli_query($conn, $Tester_query) or 
			    die("Помилка сервера при запиті $Tester_query : ".mysqli_error($conn));
	$tr = ""; // $question_ids = array(); $iq = 0;
	while ($Tester_row = mysqli_fetch_array($Tester_result1)) {
    // Друкуємо бали (норм. і набраний), якщо нормативний не пустий
	   $qi = ($normbals[$Tester_row['questionId']] != "") ? $Tester_row['questionId'] : "";
	   $qb = ($normbals[$Tester_row['questionId']] == "") ? 
				"style=\"font-weight: 600;\"" : "style=\"font-weight: normal;\"";
	   $nb = ($normbals[$Tester_row['questionId']] != "") ? 
				$normbals[$Tester_row['questionId']] : "";
	   $gb = ($normbals[$Tester_row['questionId']] != "") ? 
				$Tester_row['digit'] : "";
		$teacherComment = /* "<input type=\"checkbox\" 
						id=\"сbx".$Tester_row['id']."\" 
						name=\"сbx".$Tester_row['id']."\" class=\"del\" />
				<label for=\"сbx".$Tester_row['id']."\" class=\"del\">
				<span style=\"font-weight: normal; font-size: 75%; display: inline;\">Змінити</span></label>" */
			"<details><summary>Розшифрування</summary>".$Tester_row['teacherComment']."</details>";
	   $ch = (	($normbals[$Tester_row['questionId']] != "") and 
					(($normbals[$Tester_row['questionId']] > 0) and ($gb > 0) 
						or ($normbals[$Tester_row['questionId']] < 0) and ($gb <= 0)) 
			) ? "<br>".$teacherComment : "";

	   $se3 = (($normbals[$Tester_row['questionId']] != "") and 
		   ($Tester_row['verifying_status_id'] == 3)) ? "checked" : "";
	   $se4 = (($normbals[$Tester_row['questionId']] != "") and 
		   ($Tester_row['verifying_status_id'] == 4)) ? "checked" : "";
	   $se0 = (($normbals[$Tester_row['questionId']] != "") and 
		   ($Tester_row['verifying_status_id'] < 3)) ? "checked" : "";
	   $rb = "
			<input type=\"radio\" name=\"radioCDC".$Tester_row['questionId']."\" value=\"3\" $se3 />
				<span style=\"font-weight: normal; font-size: 80%; display: inline;\">Підтвердити</span>
			<br>
			<input type=\"radio\" name=\"radioCDC".$Tester_row['questionId']."\" value=\"4\" $se4 />
				<span style=\"font-weight: normal; font-size: 80%; display: inline;\">Відхилити</span>
			<br>
			<input type=\"radio\" name=\"radioCDC".$Tester_row['questionId']."\" value=\"0\" $se0 />
				<span style=\"font-weight: normal; font-size: 80%; display: inline;\">Відкласти</span>
			";
		$rb = (($normbals[$Tester_row['questionId']] != "") or 
			(($normbals[$Tester_row['questionId']] != 0) and 
			(($normbals[$Tester_row['questionId']] > 0) and ($gb > 0) 
			or ($normbals[$Tester_row['questionId']] < 0) and ($gb <= 0))) 
			) ? $rb : "";
		// Стиль для підтверджених балів
		$gb_style = ($se3 == "checked") ? 
						"background-color: lime; font-weight: bold;" : "";
	   $tr .= "<tr> <td style=\"text-align: center;\">".$qi.
						"</td><td><span $qb>".$questions[$Tester_row['questionId']]."</span>".
						"</td><td style=\"text-align: center;\">".$nb.
						"</td><td style=\"text-align: center; $gb_style\" >".$gb.$ch.
						"</td><td style=\"text-align: left;\">".$rb.
						"</td><td>".((!empty($rb)) ? 
						"<textarea rows=3 name=\"txa".$Tester_row['questionId']."\" >".$Tester_row['comment']."</textarea>" : "").
						"</td></tr>";
//	    $question_ids[$iq] = $qi; $iq++;
	}
	$tb = ($editMode) ? "<tr> <th colspan=5></th>
		<th><input type=\"submit\" name=\"save\" value=\"Зберегти\"></th> </tr>" : "";
	$page .= tableWrapper($th.$tr.$tb); // var_dump($question_ids);
}
