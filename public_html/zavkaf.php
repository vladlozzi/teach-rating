<?php
if(!defined("IN_ADMIN")) die;
$_GET['id'] = (isset($_GET['id'])) ? $_GET['id'] : "";
$_GET['did'] = (isset($_GET['did'])) ? $_GET['did'] : "";
$_GET['fid'] = (isset($_GET['fid'])) ? $_GET['fid'] : "";
$did = $dep_id; $putData = "";
$page .= "<p style=\"text-align: center; font-size: 133%;\">
				Підтверджений рейтинг науково-педагогічних працівників кафедри</p>";
$qplace = "SELECT * FROM `cPlace` ORDER BY `rank`";
$res = mysqli_query($conn, $qplace) or die($qplace." ".mysqli_error($conn));
$sum = 0; $sumver = 0; $stavka = 0; $deksum = 0;
$subpage = "";
while($row = mysqli_fetch_array($res)) {
	$placeName = $row['placeName'];
	$placeId = $row['id'];
	$q1 = "SELECT * FROM rating WHERE departmentId = $dep_id AND place = $placeId ORDER BY digit DESC";
	$result = mysqli_query($conn, $q1) or die($q1."<br>".mysqli_error($conn));
	$q2 = "SELECT * FROM puts WHERE departmentId = $dep_id AND place = $placeId ORDER BY digit DESC";
	$result2 = mysqli_query($conn, $q2) or die($q2."<br>".mysqli_error($conn));
//---------------------------------------------------------------------------------------------------
	$index = 1;
	$ztable_rows = tableRowWrapper(tableDigitWrapper
							("Рейтинговий список за посадою: ".bold($placeName), "colspan=\"4\"")); 
	$numbers = 0; 
	while($qrow = mysqli_fetch_array($result)) {
		$numbers++;
//=----------------
		$link = "<a href=/";
		if ($_GET['id'] != $qrow['matrixId']) {
			$link = "<a href=/?id=".$qrow['matrixId'];
			if($_GET['did']) {
				$link .= "&did=".$_GET['did'];
			}
		} else {
			if($_GET['did']) {
                $link .= "?did=".$_GET['did'];
			} 
		}
		if ($_GET['fid']) {
			$link .= "&fid=".$_GET['fid'];
		}
		$link .= ">".$qrow['name']."</a>";

	//}
		$balver = 0; // підтверджена сума балів викладача
		if (mysqli_num_rows($result2) > 0) {
			mysqli_data_seek($result2, 0); // на початок result
			while ($rrow = mysqli_fetch_array($result2)) {
				// перевірити, чи є викладач MatrixId серед підтверджених, в result
				if ($rrow['matrixId'] == $qrow['matrixId']) { $balver = $qrow['digit']; break; }
			}		
		}
		$ztable_rows .= tableRowWrapper(
   				   	tableDigitWrapper(centerWrap($index)).
							tableDigitWrapper($link). //"<a href=/?id=".$rrow['matrixId'].">".$rrow['name']."</a>").
							tableDigitWrapper(centerWrap(round($qrow['stavka'], 2))).
							tableDigitWrapper(centerWrap($balver." (".$rrow['digit'].")"))
						);
		$sum += $rrow['digit'];
		$sumver += $balver;
		$stavka += $qrow['stavka'];
		$index++;
		if ($_GET['id'] == $qrow['matrixId']) {
			$place_query="SELECT question.* from question where role = 2 order by question.id";

			$place_result = mysqli_query($conn, $place_query);
			$place = $qrow['matrixId'];
			require "record_selector.php";
			$ztable_rows .= $quest_rows;
		}//echo $index;
	}
	//$numbers += $index;
	if($_GET['did']) {
		$table_rows .= $ztable_rows;//tableWrapper($ztable_rows);
	} else {
		$subpage .= $ztable_rows;
	}
}
$page .= tableWrapper(
				tableRowWrapper(
               tableHeaderWrapper("№").
               tableHeaderWrapper("Прізвище, ім'я та по батькові").
               tableHeaderWrapper("Частка ставки").
               tableHeaderWrapper("Бали підтверджені<br>(внесені)")
            ).$subpage
			);
//$dsq= "select sum(digit) as d from rating where teacherId = ".$user_id." and departmentId = ".$fdepartment;
//$dsr = mysqli_query($conn, $dsq);
//$dsrow = mysqli_fetch_array($dsr);
//$deksum = $dsrow['d'];
$teachersCount_query = "
	SELECT COUNT(*) AS tcount 
	FROM $controwl_db.catalogTeacher 
	WHERE kaf_link = $dep_id AND role = 2 AND
		NOT (
			INSTR(teacher_surname, '~') OR
			INSTR(teacher_surname, '^')
	)
";
$teachersCount_result = mysqli_query($conn, $teachersCount_query) or die($teachersCount_query." ".mysqli_error($conn));
$teachersCount_row = mysqli_fetch_array($teachersCount_result);
$teachersCount = $teachersCount_row['tcount'];
if(!$_GET['did']) {
	// $rate=round($sumver / $stavka, 2) + $deksum;
	$rate=round($sumver / $teachersCount, 2) + $deksum;
	$page .= tableWrapper(
					tableRowWrapper("<td style=\"text-align: right;\">
						<span style=\"display: inline; font-size: 120%;\">Сумарна кількість викладачів / ставок:</span></td>
						<td style=\"text-align: left;\">
						<span style=\"display: inline; font-size: 120%;\">".$teachersCount." / ".round($stavka, 2)."</span></td>").
					tableRowWrapper("<td style=\"text-align: right;\">
						<span style=\"display: inline; font-size: 120%;\">Сума підтверджених (внесених) балів:</span></td>
						<td style=\"text-align: left;\">
						<span style=\"display: inline; font-size: 120%;\">".$sumver." (".$sum.")</span></td>").
					tableRowWrapper("<td style=\"text-align: right;\">
						<span style=\"display: inline; font-size: 120%;\">Рейтинговий показник кафедри за підтвердженими балами:</span></td>
						<td style=\"text-align: left;\">
					<span style=\"display: inline;font-size: 120%;\">".round($rate, 0)."</span>")
		).tableWrapper(
				tableRowWrapper("<td rowspan=2>Примітки.</td>
										<td><i>1. У рейтинг кафедри будуть зараховані лише ті бали викладачів, 
										які підтверджені відділами і директором інституту</i></td>").
				tableRowWrapper("<td><i>2. Рейтинг діяльності завідувача кафедри 
													дорівнює рейтингу кафедри</i></td>")
		);
}
?>
