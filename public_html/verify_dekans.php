<?php
if(!defined("IN_ADMIN")) die;
// echo "test_depart OK";
// require "chekers.php";
$questions = array(); // назви рейтингових показників
$normbals = array(); // нормативні бали рейтингових показників
$Question_query = "SELECT * FROM question ORDER BY id";
$Question_result = mysqli_query($conn, $Question_query) or 
			    die("Помилка сервера при запиті $Question_query : ".mysqli_error($conn));
while ($Question_row = mysqli_fetch_array($Question_result)) {
    $questions[$Question_row['id']] = $Question_row['name']; 
    $normbals[$Question_row['id']] = $Question_row['maxDigit'];
}

$page .= "<br>Підтвердіть рейтингові показники директорів інститутів / викладачів. 
				&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp;  
				<input type=\"button\" name=\"refresh\" value=\"Оновити список\"
						onclick=\"history.go(0)\">
				<br>Кількість директорів / викладачів, які надіслали запити на підтвердження: ";

$DekansCount_query = "SELECT COUNT(DISTINCT(a.matrixId)) AS d_cnt
			FROM teachResult a, teachMatrixNames b, question c
			WHERE a.verifying_status_id = 2 and a.tester_id = ".$_SESSION['user_id']." and 
				b.id = a.matrixId and c.magicField = '' and c.id = a.questionId";
$DekansCount_result = mysqli_query($conn, $DekansCount_query) or 
			    die("Помилка сервера при запиті $DekansCount_query : ".mysqli_error($conn));
$DekansCount_row = mysqli_fetch_array($DekansCount_result);
$page .= bold($DekansCount_row['d_cnt'])."<br>";
if ($DekansCount_row['d_cnt'] == 0) return;

$tr = "<tr><td style=\"text-align: right;\">Виберіть інститут / викладача:</td>";
$MatrixSelect_query =  "SELECT DISTINCT a.matrixId, 
											CONCAT(b.fakultet,\" - \",b.name) as dekan
								FROM teachResult a, teachMatrixNames b, question c
								WHERE b.id = a.matrixId and 
										c.magicField = '' and c.id = a.questionId and 
										a.verifying_status_id = 2 and a.tester_id = ".$_SESSION['user_id']." 
								ORDER BY b.fakultet";
/* $page .= bold($MatrixSelect_query)."<br>";
$MatrixSelect_result = mysqli_query($conn, $MatrixSelect_query) or 
			    die("Помилка сервера при запиті $MatrixSelect_query");
while ($MatrixSelect_row = mysqli_fetch_array($MatrixSelect_result))
	$page .= $MatrixSelect_row['matrixId'].": ".$MatrixSelect_row['teacher']."<br>";
*/
$tmmatrixId = (isset($_POST['dekan'])) ? $_POST['dekan'] : "";

$tr .= "<td>".selectCommonChecker("dekan", $MatrixSelect_query, 
									"matrixId", $tmmatrixId, "dekan")."</td></tr>";
$page .= tableWrapper($tr);

if (!empty($_POST['dekan'])) { // інститут вибрано
//	$page .= $_POST['teacher']; 
	$Tester_query =  "SELECT *	FROM teachResult a 
										WHERE a.tester_id = ".$_SESSION['user_id']." and a.matrixId = '".$_POST['dekan'].
									"'	ORDER BY a.questionId";

//	$page .= bold($Tester_query)."<br>";
	$Tester_result = mysqli_query($conn, $Tester_query) or 
			    die("Помилка сервера при запиті $Tester_query : ".mysqli_error($conn));
	$question_ids = array(); $iq = 0;
	while ($Tester_row = mysqli_fetch_array($Tester_result)) {
	   $qi = ($normbals[$Tester_row['questionId']] != "") ? $Tester_row['questionId'] : "";
	    $question_ids[$iq] = $qi; $iq++;
	}
	if (!empty($_POST['save'])) { // var_dump($question_ids);
	   foreach ($question_ids as $qi) {
			if (!empty($qi) and !empty($_POST['radioCDC'.$qi])) {
				$update_tR_query = "update teachResult 
									    set verifying_status_id = \"".$_POST['radioCDC'.$qi]."\" 
									    where tester_id = ".$_SESSION['user_id']." and 
											    matrixId = ".$_POST['dekan']." and
											    questionId = $qi";
				// echo "<br>".$update_tR_query;
				$update_tR_result = mysqli_query($conn, $update_tR_query) or 
									    die("Помилка сервера при запиті $update_tR_query : ".mysqli_error($conn));
			}
	   }
	}
  $th = "<tr>	<th> ID </th><th> Рейтинговий показник </th><th>Норматив,<br>бали</th>
					<th>Набрані<br>бали</th><th> Рішення про підтвердження </th></tr>";
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
		$gb = ($normbals[$Tester_row['questionId']] != "") ? $Tester_row['digit'] : "";
		$teacherComment = /* "<input type=\"checkbox\" 
						id=\"сbx".$Tester_row['id']."\" 
						name=\"сbx".$Tester_row['id']."\" class=\"del\" />
				<label for=\"сbx".$Tester_row['id']."\" class=\"del\">
				<span style=\"font-weight: normal; font-size: 75%; display: inline;\">Змінити</span></label>" */
			"<details><summary>Розшифрування</summary>".$Tester_row['teacherComment']."</details>";
	   $se3 = (($normbals[$Tester_row['questionId']] != "") and 
		   ($Tester_row['verifying_status_id'] == 3)) ? "checked" : "";
	   $se4 = (($normbals[$Tester_row['questionId']] != "") and 
		   ($Tester_row['verifying_status_id'] == 4)) ? "checked" : "";
	   $se0 = (($normbals[$Tester_row['questionId']] != "") and 
		   ($Tester_row['verifying_status_id'] < 3)) ? "checked" : "";
	   $rb = "<input type=\"radio\" name=\"radioCDC".$Tester_row['questionId']."\" 
			    value=\"3\" $se3 /> Підтвердити<br>
		    <input type=\"radio\" name=\"radioCDC".$Tester_row['questionId']."\" 
			    value=\"4\" $se4 /> Відхилити<br>
		    <input type=\"radio\" name=\"radioCDC".$Tester_row['questionId']."\" 
			    value=\"0\" $se0 /> <span style=\"font-weight: normal; display: inline\">
												Відкласти</span>";
	   $rb = ($normbals[$Tester_row['questionId']] != "") ? $rb : "";
	   $gb_style = ($se3 == "checked") ? 
						"background-color: lime; font-weight: bold;" : ""; // echo $se3." ".$gb_style;		
	   $tr .= "<tr> <td style=\"text-align: center;\">".$qi.
						"</td><td><span $qb>".$questions[$Tester_row['questionId']]."</span>".
						"</td><td style=\"text-align: center;\">".$nb.
						"</td><td style=\"text-align: center; $gb_style\">".$gb.((!empty($gb)) ? $teacherComment : "").
						"</td><td style=\"text-align: left;\">".$rb.
						"</td></tr>";
//	    $question_ids[$iq] = $qi; $iq++;
	}
	$tb = ($editMode) ? "<tr> <th colspan=4></th>
		<th><input type=\"submit\" name=\"save\" value=\"Зберегти\"></th> </tr>" : "";
	$page .= tableWrapper($th.$tr.$tb); // var_dump($question_ids);
}
?>
