<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<title>Рейтинг НПП :: ІФНТУНГ</title>
<head>
<body>
<p id="blink" class="blink" hidden 
	style="text-align: center; color: blue; font-weight: bold; font-size: 120%;"
	onload='document.getElementById("blink").hidden = true;' >
		Зачекайте, сервер обробляє інформацію...</p>
<p id="closing" class="blink" hidden 
	style="text-align: center; color: blue; font-weight: bold; font-size: 120%;"
	onload='document.getElementById("closing").hidden = true;' >
		Зачекайте, сервер закриває зʼєднання...</p>
<p style="text-align: right;">
	<span style="font-weight: bold; font-size: 120%; font-family: sans-serif;">Архів:</span> &nbsp;
	<a href="https://trate2016.nung.edu.ua" target=_blank>за 2016р.</a> &nbsp;  &nbsp; 
	<a href="https://trate2017.nung.edu.ua" target=_blank>за 2017р.</a> &nbsp;  &nbsp; 
	<a href="https://trate2018.nung.edu.ua" target=_blank>за 2018р.</a> &nbsp;  &nbsp; 
	<a href="https://trate2019.nung.edu.ua" target=_blank>за 2019р.</a> &nbsp;  &nbsp; 
	<a href="https://trate2020.nung.edu.ua" target=_blank>за 2020р.</a>
</p>
<div id="wrapper">
<?php

ini_set("error_reporting",E_ALL); ini_set("display_errors",1); ini_set("display_startup_errors",1);
ini_set("session.gc_maxlifetime", 86400); ini_set("session.save_path","./sessions");

// echo "Superglobals setting (variables_order) = ".ini_get("variables_order");

define("IN_ADMIN", TRUE);
require "logger.php";
require "auth.php"; require "auth_google.php";
	require "header.php";
	$page .= "<div id=\"content-wrapper\">";
		//require "error.php"; //if need
		echo "<h1><center>25.09.2021р. Доступ закрито через підготовку<br>до нового навчального року</center></h1>";
		echo "<h2><center>Рейтинг НПП за 2020р. в архіві за посиланням угорі. Адміністратор</center><h2>";
//		require "main.php";
	$page .= "</div>";
	require "footer.php";
echo $page;
?>
</div>
</body>
</html>
