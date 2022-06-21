<?php
if(!defined("IN_ADMIN")) die;
$date_of_close = "2021-09-07"; $editMode = date("Y-m-d") < $date_of_close; // режим редагування
$attempt_query = "select count(id) AS cid from brutLog where ip = '".$_SERVER['REMOTE_ADDR']."'";
$attempt_result = mysqli_query($conn, $attempt_query) or die(mysqli_error($conn));
if ($maxAttempt_row = mysqli_fetch_array($attempt_result)) {
	$maxAttempt = $maxAttempt_row['cid'];
}
if (isset($_SESSION['user_role']) && ($maxAttempt<10000)) {
	require "merge_fp.php"; // (об'єднуємо усі секції кафедри ФП)
	require "tegs.php";
	require "logout.php";
	$page .= "<form method=\"post\" target=\"_self\">";
	require "common/greeting.php";
	switch ($_SESSION['user_role'])
	{
		case 'ROLE_TEACHER' :
			// викладач - вводить значення своїх рейтингових показників
			$user_id = $_SESSION['user_id']; // echo "У ролі Декан - " . $_SESSION['user_isDekan'];
//			if ($user_id == 1411) {
				require "teacher_record.php";
//			} else $page .= "<h3>Увага! Відбувається закрите тестування системи з новими рейтинговими показниками!</h3>";
			break;
		case 'ROLE_TEST_DEP':
			// контрольний відділ (кадрів, науково-технічний, навчальний)
			// перевіряє і підтверджує рейтингові показники від викладачів
			$user_id = $_SESSION['user_id']; require "verify_teachers.php"; break;
		case 'ROLE_ZAVKAF' :
			// зав. кафедри - переглядає рейтинг кафедри
			$tnumb = 0;
			$dep_id = $_SESSION['user_description']; require "teachers_registered.php"; require "zavkaf.php"; break;
/*		case 'ROLE_DEKANAT' : // деканат - перевіряє показники ОВР від викладачів
			$user_id = $_SESSION['user_id']; require "dekanat.php"; break;
*/		case 'ROLE_DEKAN' :
				// директор інституту - вводить значення своїх показників ОВР;
				// - перевіряє і підтверджує показники від викладачів;
				// - переглядає рейтинг НПП інституту
			require "dekan.php"; break;
		case 'ROLE_RECTOR' :
			// ректор - переглядає рейтинг усіх НПП
			require "rector.php"; break;
		case 'ROLE_VICERECTOR' :
			// проректор із НПР - перевіряє і підтверджує рейтингові показники;
			// переглядає рейтинг усіх НПП
			require "rector.php"; break;
		case 'ROLE_VICERECTOR_ASSISTANT' :
			// помічник проректора з НПР - переглядає рейтинг усіх НПП
			require "rector.php"; break;
		case 'ROLE_ADMIN' :
			// адміністратор - переглядає рейтинг усіх НПП
			require "rector.php"; break;
		case 'ROLE_STPROF' :
			// голова профспілкової організації - підтверджує показники директорів інститутів
			require "stud_org.php"; break;
		case 'ROLE_STUDENT_PARLAMENT' :
			// голова профспілкової організації - підтверджує показники директорів інститутів
			require "stud_org.php"; break;
	}
	$page.= "</form>";
} else {
	require "news.php";
	require "login.php";
}
