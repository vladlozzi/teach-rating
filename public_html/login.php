<?php
if(!defined("IN_ADMIN")) die;

$params = array(
	'client_id'     => '95940988915-3o9u1oh6dim29o67l6glmkr4n87lvkp9.apps.googleusercontent.com',
	'redirect_uri'  => 'https://teach-rating.nung.edu.ua/index.php',
	'response_type' => 'code',
	'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
	'state'         => '123'
);
$url = 'https://accounts.google.com/o/oauth2/auth?' . urldecode(http_build_query($params));

$login1 = <<< EOT
<form id="login" action="" method="post">
	<div class="login-caption"><h1>Авторизуйтесь</h1></div>
	<fieldset id="inputs">
		<input id="username" type="text" name="login" placeholder="Логін" autofocus required />
		<input id="password" type="password" name="psswd" placeholder="Пароль" required />
	</fieldset>
	<fieldset id="actions">
		<input type="hidden" name="enter" value="yes" />
		<input type="submit" id="submit" value="Вхід" 
			onclick='document.getElementById("blink").hidden = false' />
	</fieldset>
<p style="text-align: center; font-weight: bold;">або <a href=
EOT;
$login2 = '"'.$url.'" ';
$login3 = <<< EOT
target="_self">увійдіть через пошту ІФНТУНГ</a></p>
</form>
EOT;
$page .= $login1.$login2.$login3;
require "common/warning.php";