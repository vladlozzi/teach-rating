<?php
if (!defined("IN_ADMIN")) die;
// echo $_GET['code']; sleep(10);
if (!empty($_GET['code'])) {
	// Надсилаємо код для отримання токена (POST-запит).
	$params = array(
		'client_id'     => '',
		'client_secret' => '',
		'redirect_uri'  => 'https://teach-rating.nung.edu.ua/index.php',
		'grant_type'    => 'authorization_code',
		'code'          => $_GET['code']
	);
	$ch = curl_init('https://accounts.google.com/o/oauth2/token');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$data = curl_exec($ch);
	curl_close($ch);
	$data = json_decode($data, true);
	// echo $data['access_token']; sleep(10);
	if (!empty($data['access_token'])) {
		// Токен отримали, одержуємо дані користувача
		$params = array(
			'access_token' => $data['access_token'],
			'id_token'     => $data['id_token'],
			'token_type'   => 'Bearer',
			'expires_in'   => 3599
		);
		$info = file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?' . urldecode(http_build_query($params)));
		$info = json_decode($info, true);
		// Авторизуємо користувача за email
		$auth_query = 'SELECT * FROM userAuth1 WHERE email = "'.mysqli_real_escape_string($conn, $info['email']).'"';
		// echo $auth_query; sleep(10);
		$auth_result = mysqli_query($conn, $auth_query) or die("Помилка сервера при запиті auth_query: ".mysqli_error($conn));
		if (mysqli_num_rows($auth_result) == 1) {
			$auth_row = mysqli_fetch_assoc($auth_result);
			$_SESSION['user_id'] = $auth_row['id'];
			$_SESSION['user_role'] = $auth_row['role'];
			$_SESSION['user_fullname'] = $auth_row['fullname'];
			$_SESSION['user_description'] = $auth_row['userDescription'];
			logData($auth_row['id'], $auth_row['role'], '0', 'logged[===]'.$auth_row['login'].'[===]'.$info['email']);
		} else {
			logData(0, 'brut', '0', $info['email'].'<===>' /* .$psswd */);
		}
	}
}