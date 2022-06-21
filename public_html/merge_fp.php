<?php
if(!defined("IN_ADMIN")) die;
// Об'єднуємо викладачів усіх секцій кафедри ФП (67) у таблиці teachMatrix
// 39 - викладачі секцій перекладу та української мови
// 66 - викладачі секцій німецької та французької мов
$MergeQuery = "
					UPDATE teachMatrix 
					SET departmentId = 67 
					WHERE departmentId = 39 OR departmentId = 66
				";
$MergeQuery_result = mysqli_query($conn, $MergeQuery) or die($MergeQuery." ".mysqli_error($conn));
?>
