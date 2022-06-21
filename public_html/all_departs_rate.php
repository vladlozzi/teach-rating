<?php
if(!defined("IN_ADMIN")) die;
$departsScoreQuery = "SELECT did AS id, suma, nazva_kaf FROM departRating ORDER by nazva_kaf";
$departsScore_result = mysqli_query($conn, $departsScoreQuery) or die($departsScoreQuery." : ".mysqli_error($conn));

$departsTeachersCountQuery = "
SELECT a.id, a.nazva_kaf, COUNT(*) AS t_cnt 
FROM 
	$controwl_db.catalogDepartment a, 
	$controwl_db.catalogTeacher b 
WHERE b.kaf_link = a.id AND a.id != 82 AND b.role = 2 AND NOT (
		INSTR(b.teacher_surname, '~') OR
		INSTR(b.teacher_surname, '^')
)
GROUP BY a.id
ORDER BY a.nazva_kaf
";
$departsTeachersCount_result = mysqli_query($conn, $departsTeachersCountQuery) or die($departsTeachersCountQuery." : ".mysqli_error($conn));

$arrDepartsScoreTeachersCount = array(); $departIndex = 0;
while ($row = mysqli_fetch_array($departsTeachersCount_result)) {
	$arrDepartsScoreTeachersCount[$departIndex]['depart'] = $row['nazva_kaf'];
	$arrDepartsScoreTeachersCount[$departIndex]['t_cnt'] = $row['t_cnt'];
	$departScoreQuery = "SELECT did AS id, suma, nazva_kaf FROM departRating WHERE did = " . $row['id'];
	$departScore_result = mysqli_query($conn, $departScoreQuery) or die($departsTeachersCountScoreQuery . " : " . mysqli_error($conn));
	if ($row2 = mysqli_fetch_array($departScore_result)) $arrDepartsScoreTeachersCount[$departIndex]['suma'] = $row2['suma'];
	else $arrDepartsScoreTeachersCount[$departIndex]['suma'] = 0;
	$departIndex++;
}

$departsCount = $departIndex; // echo $departsCount;
for ($departIndex = 0; $departIndex < $departsCount; $departIndex++) {
	$arrDepartsScoreTeachersCount[$departIndex]['rate'] = 
		round($arrDepartsScoreTeachersCount[$departIndex]['suma'] / $arrDepartsScoreTeachersCount[$departIndex]['t_cnt'], 0);
}

// Сортуємо в порядку спадання балів
$rate = array_column($arrDepartsScoreTeachersCount, 'rate');
$depart = array_column($arrDepartsScoreTeachersCount, 'depart');
array_multisort($rate, SORT_DESC, $depart, SORT_ASC, $arrDepartsScoreTeachersCount);

$tableRows = tableRowWrapper(tableHeaderWrapper("№").tableHeaderWrapper("Кафедра").tableHeaderWrapper("Бали"));
for ($departIndex = 0; $departIndex < $departsCount; $departIndex++) {
	$tableRows .= tableRowWrapper(tableDigitWrapper(centerWrap($departIndex + 1)).
		tableDigitWrapper($arrDepartsScoreTeachersCount[$departIndex]['depart']).
		tableDigitWrapper(centerWrap($arrDepartsScoreTeachersCount[$departIndex]['rate']))
	);
}
$page .= '
	<p style="font-size: 125%; text-align: center;">
		Рейтинг кафедр
	</p>
' . tableWrapper($tableRows, 'style="width: 55%; margin-left: auto; margin-right: auto;"').'
	<p style="font-size: 90%; text-align: center;">
		<em>Для визначення рейтингу враховуються бали, підтверджені уповноваженими особами.</em>
	</p>
';
