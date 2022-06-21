<?php
if(!defined("IN_ADMIN")) die;
//before use this script please set $dep_id
//$q = "SELECT *, SUM(digit) FROM teachMatrix WHERE departmentId = '".$_SESSION['user_description']."' group by ";
//echo $q;
$qcat = "SELECT categoryName, ANY_VALUE(categoryRank) FROM cPlace GROUP BY categoryName ORDER BY ANY_VALUE(categoryRank)";
$qcat_result = mysqli_query($conn, $qcat) or die($qcat." : ".mysqli_error($conn));
$sum = 0; $tnumb = 0; $stavka = 0;
$_GET['did'] = (isset($_GET['did'])) ? $_GET['did'] : 0;
while($row = mysqli_fetch_array($qcat_result)) {
	$catName = $row['categoryName']; // echo $catName." ";
	// які посади входять у цю групу посад
	$qplace = "SELECT * FROM cPlace WHERE categoryName = \"".$catName."\"";
	$qplace_result = mysqli_query($conn, $qplace) or die($qplace." : ".mysqli_error($conn));
	$placeId = "";
	while($qplace_row = mysqli_fetch_array($qplace_result)) {
		$placeId .= "place = ".$qplace_row['id']." OR ";
	}
	$placeId = rtrim($placeId, " OR ");
	$q = "SELECT * FROM rating WHERE ".((empty($placeId)) ? "1" : $placeId); // echo $q."<br>";
	$result = mysqli_query($conn, $q) or die($q." : ".mysqli_error($conn));
//---------------------------------------------------------------------------------------------------
	$index = 1; $table_rows = "";
	$ztable_rows = tableRowWrapper(tableDigitWrapper(centerWrap(bold($catName)), "colspan=\"6\"")). 
		tableRowWrapper(
                tableHeaderWrapper("№").
                tableHeaderWrapper("Прізвище, ім'я та по батькові").
                tableHeaderWrapper("Інститут").
                tableHeaderWrapper("Кафедра").
                tableHeaderWrapper("Частка ставки").
                tableHeaderWrapper("Бали")
                );
	while($qrow = mysqli_fetch_array($result)) {
//		$numbers++;

		$link = "<a href=/";
		if ($_GET['id'] != $qrow['matrixId']) {
			$link = "<a href=/?allteachrate=1&id=".$qrow['matrixId'];
		} else { 
			$link = "<a href=/?allteachrate=1";
		}
		$link .= ">".$qrow['name']."</a>";

		$ztable_rows .= tableRowWrapper(
			tableDigitWrapper(centerWrap($index)).
			tableDigitWrapper($link).
			tableDigitWrapper(centerWrap($qrow['fakultet'])).
			tableDigitWrapper($qrow['nazva_kaf']).
			tableDigitWrapper("&nbsp;&nbsp;".round($qrow['stavka'], 2)).
			tableDigitWrapper(centerWrap($qrow['digit']))
		);
		$sum += $qrow['digit']; $stavka += $qrow['stavka']; $index++;
		if ($_GET['id'] == $qrow['matrixId']) {
			$_POST['all_teachers'] = 1;
			$place_query="SELECT question.* from question where role = 2 order by question.id";
			$place_result = mysqli_query($conn, $place_query) or die($place_query." : ".mysqli_error($conn));
			$place = $qrow['matrixId']; require "record_selector.php"; $ztable_rows .= $quest_rows;
		} //echo $index;
	}
	$tnumb += $index - 1;
	
	if($_GET['did']) {
		$table_rows .= $ztable_rows;//tableWrapper($ztable_rows);
	} else {
		$page .= '<p style="font-size: 125%; text-align: center;">
			Рейтинг науково-педагогічних працівників кафедр
			</p>
		' . tableWrapper($ztable_rows);
	}
}
if(!$_GET['did']) {
	$page .= tableWrapper(
		tableRowWrapper(tableDigitWrapper(
			"<span style=\"text-align: right;\">Загальна кількість викладачів:&nbsp;</span>"
		).tableDigitWrapper("<span>".$tnumb."</span>")).
		tableRowWrapper(tableDigitWrapper(
			"<span style=\"text-align: right;\">Сума рейтингових балів:&nbsp;</span>"
		).tableDigitWrapper("<span>".$sum."</span>")).
		tableRowWrapper(tableDigitWrapper(
			"<span style=\"text-align: right;\">Середня кількість балів на 1 викладача:&nbsp;</span>").
		tableDigitWrapper("<span>".round($sum/$tnumb, 0))."</span>").
		tableRowWrapper(tableDigitWrapper(
			"<span style=\"text-align: right;\">Середня кількість балів на 1 ставку:&nbsp;</span>").
		tableDigitWrapper("<span>".round($sum/$stavka, 0))."</span>")
	).'
		<p style="font-size: 90%; text-align: center;">
			<em>Для визначення рейтингу враховуються бали, підтверджені уповноваженими особами.</em>
		</p>
	';
}

