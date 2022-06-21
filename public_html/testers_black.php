<?php
if(!defined("IN_ADMIN")) die;
$TestersBlack1_query = "SELECT a.id, a.dekan_name, COUNT(*) AS rest
											FROM ".$controwl_db.".catalogDekan a, teachResult b
											WHERE a.role = 17 AND b.tester_id = a.id AND b.verifying_status_id = 2
											GROUP BY a.id
											";
$TestersBlack1_result = mysqli_query($conn, $TestersBlack1_query) or 
			    die("Помилка сервера при запиті $$TestersBlack1_query : ".mysql_error($conn));
$page .= "<p style=\"font-size: 125%; text-align: center; margin-top: 0.5em; margin-bottom: 0.2em;\">
					Підрозділи, які не закінчили перевірку рейтингових показників викладачів:</p>";
if (mysqli_num_rows($TestersBlack1_result) == 0) $page .= "- таких підрозділів немає"; else
	while ($TestersBlack1_row = mysqli_fetch_array($TestersBlack1_result)) {
		$page .= "- ".str_replace(" (рейтинг)", "", mb_strtolower($TestersBlack1_row['dekan_name'])).
						" - не перевірено показників: ".bold($TestersBlack1_row['rest'])."<br>";
	}

$TestersBlack2_query = "SELECT a.id, a.dekan_name, COUNT(*) AS rest
												FROM ".$controwl_db.".catalogDekan a, teachResult b, teachMatrixNames c
												WHERE a.role = 4 AND b.verifying_status_id = 2 AND b.tester_id = 32 AND 
															b.matrixId = c.id AND c.faculty_id = a.fakul_id
												GROUP BY a.id ORDER BY a.dekan_name";
$TestersBlack2_result = mysqli_query($conn, $TestersBlack2_query) or 
			    die("Помилка сервера при запиті $$TestersBlack2_query : ".mysqli_error($conn));
$page .= "<p style=\"font-size: 125%; text-align: center; margin-top: 0.5em; margin-bottom: 0.2em;\">
					Директори інститутів, які не закінчили перевірку рейтингових показників викладачів:</p>";
if (mysqli_num_rows($TestersBlack2_result) == 0) $page .= "- таких директорів немає"; else
	while ($TestersBlack2_row = mysqli_fetch_array($TestersBlack2_result)) {
		$page .= "- ".$TestersBlack2_row['dekan_name'].
						" - не перевірено показників: ".bold($TestersBlack2_row['rest'])."<br>";
	}

$TestersBlack3_query = "SELECT b.departmentId AS fid, c.fakultet_name AS fname, COUNT(*) AS rest
												FROM teachResult a, teachMatrix b, cFakultet c, question d
												WHERE a.tester_id = 79 AND a.verifying_status_id = 2 AND 
															b.id = a.matrixId AND c.id = b.departmentId AND 
															d.id = a.questionId AND d.magicField = ''
												GROUP BY c.id ORDER BY fname
											";
$TestersBlack3_result = mysqli_query($conn, $TestersBlack3_query) or 
			    die("Помилка сервера при запиті $$TestersBlack3_query : ".mysqli_error($conn));
$page .= "<p style=\"font-size: 125%; text-align: center; margin-top: 0.5em; margin-bottom: 0.2em;\">
					Проректор не закінчив перевірку рейтингових показників директорів таких інститутів:</p>";
if (mysqli_num_rows($TestersBlack3_result) == 0) $page .= "- таких інститутів немає"; else
	while ($TestersBlack3_row = mysqli_fetch_array($TestersBlack3_result)) {
		$page .= "- ".str_replace("інститут ", "", str_replace(" інститут", "", 
										mb_strtolower($TestersBlack3_row['fname']))).
						" - не перевірено показників: ".bold($TestersBlack3_row['rest'])."<br>";
	}

?>
