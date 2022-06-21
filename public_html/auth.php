<?php  
if(!defined("IN_ADMIN")) die;
require "dbu.php";
session_start();
if(!empty($_GET['logout'])) {
	session_destroy();
	header ("Location: /");
}
if(!empty($_POST['enter'])) {
        $_SESSION['login'] = mysqli_real_escape_string($conn, $_POST['login']);
        $_SESSION['psswd'] = mysqli_real_escape_string($conn, $_POST['psswd']);  
}
//===============================================================================
function escape_inj ($text) {
  $text = strtolower($text); // Приравниваем текст параметра к нижнему регистру
  if (
    !strpos($text, "select") && // 
    !strpos($text, "union") && //
    !strpos($text, "select") && //
    !strpos($text, "order") && // Ищем вхождение слов в параметре
    !strpos($text, "where") && // 
    !strpos($text, "char") && //
    !strpos($text, "from") //
  ) {
    return true; // Вхождений нету - возвращаем true
  } else {
    return false; // Вхождения есть - возвращаем false
  }
}
//================================================================================
if(isset($_SESSION['login']) && isset($_SESSION['psswd'])) {
	$login = $_SESSION['login'];
	$psswd = $_SESSION['psswd'];
	if(escape_inj($login) && escape_inj($psswd)) {
		$query = "SELECT * FROM `userAuth1` WHERE `login`='".md5($login)."' AND `psswd`='".md5($psswd)."' LIMIT 1";
		$sql = mysqli_query($conn, $query) or die("Помилка при виконанні ".$query." :<br>".mysqli_error($conn));
		if (mysqli_num_rows($sql) == 1) {
			$row = mysqli_fetch_assoc($sql);
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['user_role'] = $row['role'];
			$_SESSION['user_fullname'] = $row['fullname'];
			$_SESSION['user_description'] = $row['userDescription'];
			$_SESSION['user_isDekan'] = $row['isDekan'];
			logData($row['id'], $row['role'], '0', 'logged[===]'.$login.'[===]'.$psswd);
		} else {
			logData(0, 'brut', '0', $login.'[===]'.$psswd);
		}
	} else {
		logData(0, 'AXTUNG!', '0', $login.'[===]'.$psswd);
	}
} else {
	$login = "";
	$psswd = "";
}
?>
