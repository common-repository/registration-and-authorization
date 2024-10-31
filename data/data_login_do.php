<?php
if(!defined('ABSPATH')) {
	$AjaxRequest = true;
} else {
	$AjaxRequest = false;
}

$DataValuesArr = [];
if($AjaxRequest) {
	if(!isset($_SERVER['HTTP_REFERER'])) {
		die();
	}
	sleep(1);
	
	if(!isset($_POST['Values'])) {
		die();
	}
	
	$bPop = false;
	if(isset($_POST['Pop'])) {
		$bPop = (int)$_POST['Pop'];
	}
	if($bPop) {
		$szPreTag = 'pop_';
	} else {
		$szPreTag = '';
	}

	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	require_once WP_PLUGIN_DIR.'/iq_authorize/includes/inc_func.php';
	
	#######################
	##### DATA VALUES #####
	#######################
	$DataJsonStrArr = array();
	$PostValues = htmlspecialchars(trim($_POST['Values']));
	$Exp = explode(',', $PostValues);
	$ArrayData = array();
	for($i = 0; $i < count($Exp); $i++) {
		$TempParam = trim($Exp[$i]);
		if(empty($TempParam)) { continue; }
		$ArrayData[] = $TempParam;
	}
	
	/*
	echo '<pre>';
	print_r($ArrayData);
	echo '</pre>';
	die();
	*/

	if(empty($ArrayData)) {
		die();
	}
	
	// iq_login
	$Key = $szPreTag.'iq_login';
	if(!in_array($Key, $ArrayData) || !isset($_POST[$Key])) {
		$szMsg = 'Введите логин или email';
		die($szMsg);
	}
	$DataValuesArr['iq_login'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_login'])) {
		$szMsg = 'Введите логин или email';
		die($szMsg);
	}
	
	// iq_pass
	$Key = $szPreTag.'iq_pass';
	if(!in_array($Key, $ArrayData) || !isset($_POST[$Key])) {
		$szMsg = 'Введите пароль';
		die($szMsg);
	}
	$DataValuesArr['iq_pass'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_pass'])) {
		$szMsg = 'Введите пароль';
		die($szMsg);
	}
} else {
	// iq_login
	$Key = $szPreTag.'iq_login';
	if(!isset($_POST[$Key]) || empty($_POST[$Key])) {
		$szErr = 'Введите логин или email';
		return;
	}
	$DataValuesArr['iq_login'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_login'])) {
		$szErr = 'Введите логин или email';
		return;
	}
	
	// iq_pass
	$Key = $szPreTag.'iq_pass';
	if(!isset($_POST[$Key]) || empty($_POST[$Key])) {
		$szErr = 'Введите пароль';
		return;
	}
	$DataValuesArr['iq_pass'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_pass'])) {
		$szErr = 'Введите пароль';
		return;
	}
}

if(!isset($iq_auth_settings_arr)) {
	$iq_auth_settings_arr = func_iq_authorization_get_settings();
}

$redirect_url = '/';
if(isset($iq_auth_settings_arr['auth_redirect'])) {
	$redirect_url = '/'.$iq_auth_settings_arr['auth_redirect'];
}

######################
##### VALIDATION #####
######################
// def inputs
$DefArr['login'] = [
	'name' => 'Логин',
	'regex' => '/^(?=.*[A-Za-zА-Яа-яёґєії0-9])[A-Za-zА-Яа-яёґєії][A-Za-zА-Яа-яёґєії\d .-]{0,19}$/',
	'min' => 2,
	'max' => 64,
];
$DefArr['email'] = [
	'name' => 'Email',
	'regex' => '/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/',
	'min' => 2,
	'max' => 64,
];
$DefArr['pass'] = [
	'name' => 'Пароль',
	'regex' => '/^[A-Za-z0-9_\-\.]+$/',
	'min' => 2,
	'max' => 64,
];

$FormVal = [];

// valid login
$bLogin = true;
$bEmail = true;

$key = 'login';
$data = $DefArr[$key];
$FormVal[$key.'_length'] = $data['min'].':'.$data['max'];
if(isset($iq_auth_settings_arr[$key.'_length'])) {
	$FormVal[$key.'_length'] = $iq_auth_settings_arr[$key.'_length'];
}
$Exp = explode(':', $FormVal[$key.'_length']);
$FormVal[$key.'_length_min'] = (int)$Exp[0];
$FormVal[$key.'_length_max'] = (int)$Exp[1];
$KeyPost = 'iq_'.$key;
if(func_iq_authorization_get_count_letter($DataValuesArr['iq_'.$key]) < $FormVal[$key.'_length_min'] || func_iq_authorization_get_count_letter($DataValuesArr['iq_'.$key]) > $FormVal[$key.'_length_max']) {
	$bLogin = false;
} else {
	// regex
	$FormVal[$key.'_regex'] = '';
	if(isset($iq_auth_settings_arr[$key.'_regex'])) {
		$FormVal[$key.'_regex'] = stripcslashes($iq_auth_settings_arr[$key.'_regex']);
	}
	if(!empty($FormVal[$key.'_regex'])) {
		if(preg_match($FormVal[$key.'_regex'], $DataValuesArr['iq_'.$key]) !== 1) {
			$bLogin = false;
		}
	}
}

if(!$bLogin) {
	// email
	$key = 'email';
	$key_login = 'login';
	$data = $DefArr[$key];
	$FormVal[$key.'_length'] = $data['min'].':'.$data['max'];
	if(isset($iq_auth_settings_arr[$key.'_length'])) {
		$FormVal[$key.'_length'] = $iq_auth_settings_arr[$key.'_length'];
	}
	$Exp = explode(':', $FormVal[$key.'_length']);
	$FormVal[$key.'_length_min'] = (int)$Exp[0];
	$FormVal[$key.'_length_max'] = (int)$Exp[1];
	$KeyPost = 'iq_'.$key;
	if(func_iq_authorization_get_count_letter($DataValuesArr['iq_'.$key_login]) < $FormVal[$key.'_length_min'] || func_iq_authorization_get_count_letter($DataValuesArr['iq_'.$key_login]) > $FormVal[$key.'_length_max']) {
		$bEmail = false;
	} else {
		// regex
		$FormVal[$key.'_regex'] = '';
		if(isset($iq_auth_settings_arr[$key.'_regex'])) {
			$FormVal[$key.'_regex'] = stripcslashes($iq_auth_settings_arr[$key.'_regex']);
		}
		if(!empty($FormVal[$key.'_regex'])) {
			if(preg_match($FormVal[$key.'_regex'], $DataValuesArr['iq_'.$key_login]) !== 1) {
				$bEmail = false;
			}
		}
	}
}

if(!$bLogin && !$bEmail) {
	$key = 'login';
	$data = $DefArr[$key];
	if(isset($iq_auth_settings_arr[$key.'_regex_text']) && !empty($iq_auth_settings_arr[$key.'_regex_text'])) {
		$szMsg = stripcslashes($iq_auth_settings_arr[$key.'_regex_text']);
	} else {
		$szMsg = 'Некорректное значение поля «'.$data['name'].'»';
	}
	if($AjaxRequest) {
		die($szMsg);
	}
	$szErr = $szMsg;
	return;
}

// pass
$key = 'pass';
$data = $DefArr[$key];

$FormVal[$key.'_length'] = $data['min'].':'.$data['max'];
if(isset($iq_auth_settings_arr[$key.'_length'])) {
	$FormVal[$key.'_length'] = $iq_auth_settings_arr[$key.'_length'];
}
$Exp = explode(':', $FormVal[$key.'_length']);
$FormVal[$key.'_length_min'] = (int)$Exp[0];
$FormVal[$key.'_length_max'] = (int)$Exp[1];
$KeyPost = 'iq_'.$key;
if(func_iq_authorization_get_count_letter($DataValuesArr['iq_'.$key]) < $FormVal[$key.'_length_min'] || func_iq_authorization_get_count_letter($DataValuesArr['iq_'.$key]) > $FormVal[$key.'_length_max']) {
	$szMsg = 'Длина поля «'.$data['name'].'» должна быть от '.$FormVal[$key.'_length_min'].' до '.$FormVal[$key.'_length_max'].' символов';
	if($AjaxRequest) {
		die($szMsg);
	}
	$szErr = $szMsg;
	return;
}
// regex
$FormVal[$key.'_regex'] = '';
if(isset($iq_auth_settings_arr[$key.'_regex'])) {
	$FormVal[$key.'_regex'] = stripcslashes($iq_auth_settings_arr[$key.'_regex']);
}
if(!empty($FormVal[$key.'_regex'])) {
	if(preg_match($FormVal[$key.'_regex'], $DataValuesArr['iq_'.$key]) !== 1) {
		if(isset($iq_auth_settings_arr[$key.'_regex_text']) && !empty($iq_auth_settings_arr[$key.'_regex_text'])) {
			$szMsg = stripcslashes($iq_auth_settings_arr[$key.'_regex_text']);
		} else {
			$szMsg = 'Некорректное значение поля «'.$data['name'].'»';
		}
		if($AjaxRequest) {
			die($szMsg);
		}
		$szErr = $szMsg;
		return;
	}
}

if ( is_user_logged_in() ) {
	if($AjaxRequest) {
		$szMsg = '
			<div id="auth_already">
				<input type="hidden" id="temp_redirect_url" value="'.$redirect_url.'">
				Вы уже авторизованы
			</div>
		';
		die($szMsg);
	}
	wp_redirect($redirect_url);
	return;
}

if(!isset($cIQAuthClass)) {
	require_once iq_authorization_CORE_DIR.'/includes/inc_auth.php';
	$cIQAuthClass = new IQAuthClass();
}

###################
##### CAPTCHA #####
###################
$service_regist = 'login_try';
$bCaptcha = false;
$bCaptchaShow = false;
if(isset($iq_auth_settings_arr['login_captcha_enable']) && (int)$iq_auth_settings_arr['login_captcha_enable']) {
	if(isset($iq_auth_settings_arr['captcha_pub']) && !empty($iq_auth_settings_arr['captcha_pub']) && isset($iq_auth_settings_arr['captcha_secret']) && !empty($iq_auth_settings_arr['captcha_secret'])) {
		$bCaptcha = true;
	}
}

$iCaptchaLimitFree = 0;
if($bCaptcha) {
	if(isset($iq_auth_settings_arr['captcha_limit_free']) && (int)$iq_auth_settings_arr['captcha_limit_free']) {
		$iCaptchaLimitFree = (int)$iq_auth_settings_arr['captcha_limit_free'];
		$iCountTry = $cIQAuthClass->antifloodGetCount($service_regist);
		if($iCountTry >= (int)$iCaptchaLimitFree) {
			$bCaptchaShow = true;
		}
	} else {
		// Always
		$bCaptchaShow = true;
	}
}

if($bCaptcha && $bCaptchaShow) {
	// Show captcha
	$capcha_key = false;
	if($AjaxRequest) {
		foreach($ArrayData AS $key) {
			// echo $key.'<br>';
			if(strpos($key, 'g-recaptcha-response') !== false) {
				if(isset($_POST[$key])) {
					$capcha_key = $key;
					break;
				}
			}
		}
	} else {
		foreach($_POST AS $key => $post_data) {
			if(strpos($key, 'g-recaptcha-response') !== false) {
				$capcha_key = $key;
				break;
			}
		}
	}
	if(!$capcha_key) {
		// echo '<pre>' . print_r($ArrayData, true) . '</pre>';
		$szErr = '
			<div id="captcha_block" class="iq_authorization_align_center">
				<center>
					<script src="https://www.google.com/recaptcha/api.js" async defer></script>
					<div class="g-recaptcha" data-sitekey="'.$iq_auth_settings_arr['captcha_pub'].'"></div>
				</center>
			</div>
		';
		if($AjaxRequest) {
			die($szErr);
		}
		return;
	}
	
	$response_key = $_POST[$capcha_key];
	
	$secretKey = $iq_auth_settings_arr['captcha_secret'];
	$ip = $_SERVER['REMOTE_ADDR'];
	$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($response_key);
	$response = file_get_contents($url);
	$responseKeys = json_decode($response,true);
	if(!$responseKeys["success"]) {
		// echo '<pre>' . print_r($ArrayData, true) . '</pre>';
		$szErr = '
			<div id="captcha_block" class="iq_authorization_align_center">
				<center>
					<script src="https://www.google.com/recaptcha/api.js" async defer></script>
					<div class="g-recaptcha" data-sitekey="'.$iq_auth_settings_arr['captcha_pub'].'"></div>
				</center>
			</div>
		';
		if($AjaxRequest) {
			die($szErr);
		}
		return;
	}
	if($iCaptchaLimitFree) {
		$cIQAuthClass->antifloodRemove($service_regist);
	}
}
if($bCaptcha && !$bCaptchaShow && $iCaptchaLimitFree) {
	$cIQAuthClass->antifloodAdd($service_regist);
}


$credentials = [
	'user_login' => $DataValuesArr['iq_login'],
	'user_password' => $DataValuesArr['iq_pass'],
	'rememberme' => true,
];

$signon = wp_signon($credentials, true); // true - use HTTP only cookie
if(is_wp_error($signon)){
	$szErr = 'Не верный логин или пароль';
	if($AjaxRequest) {
		die($szErr);
	}
	return;
}
$AuthObj = wp_authenticate( $DataValuesArr['iq_login'], $DataValuesArr['iq_pass'] );
$AuthArr = (array)$AuthObj;

if(isset($AuthArr['data'])) {
	// Успешная авторизация
	if($AjaxRequest) {
		$szMsg = '
			<div id="success">
				<input type="hidden" id="temp_redirect_url" value="'.$redirect_url.'">
				Вы успешно авторизовались
			</div>
		';
		die($szMsg);
	}
	wp_redirect($redirect_url);
	return;
}
if(isset($AuthArr['errors'])) {
	foreach($AuthArr['errors'] AS $key => $data) {
		$szErr = $data[0];
		break;
	}
}
if(!isset($szErr) || empty($szErr)) {
	$szErr = 'Ошибка авторизации';
}
if($AjaxRequest) {
	die($szErr);
}
return;
?>