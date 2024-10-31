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
		$szMsg = 'Введите желаемый логин';
		die($szMsg);
	}
	$DataValuesArr['iq_login'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_login'])) {
		$szMsg = 'Введите желаемый логин';
		die($szMsg);
	}
	
	// iq_email
	$Key = $szPreTag.'iq_email';
	if(!in_array($Key, $ArrayData) || !isset($_POST[$Key])) {
		$szMsg = 'Введите email';
		die($szMsg);
	}
	$DataValuesArr['iq_email'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_email'])) {
		$szMsg = 'Введите email';
		die($szMsg);
	}
	
	// iq_pass
	$Key = $szPreTag.'iq_pass';
	if(!in_array($Key, $ArrayData) || !isset($_POST[$Key])) {
		$szMsg = 'Введите желаемый пароль';
		die($szMsg);
	}
	$DataValuesArr['iq_pass'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_pass'])) {
		$szMsg = 'Введите желаемый пароль';
		die($szMsg);
	}
	
	// iq_repass
	$Key = $szPreTag.'iq_repass';
	if(!in_array($Key, $ArrayData) || !isset($_POST[$Key])) {
		$szMsg = 'Введите повтор пароля';
		die($szMsg);
	}
	$DataValuesArr['iq_repass'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_repass'])) {
		$szMsg = 'Введите повтор пароля';
		die($szMsg);
	}
	
	if($DataValuesArr['iq_pass'] != $DataValuesArr['iq_repass']) {
		$szMsg = 'Пароли не совпадают';
		die($szMsg);
	}
} else {
	// iq_login
	$Key = $szPreTag.'iq_login';
	if(!isset($_POST[$Key]) || empty($_POST[$Key])) {
		$szErr = 'Введите желаемый логин';
		return;
	}
	$DataValuesArr['iq_login'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_login'])) {
		$szErr = 'Введите желаемый логин';
		return;
	}
	
	// iq_email
	$Key = $szPreTag.'iq_email';
	if(!isset($_POST[$Key]) || empty($_POST[$Key])) {
		$szErr = 'Введите email';
		return;
	}
	$DataValuesArr['iq_email'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_email'])) {
		$szErr = 'Введите email';
		return;
	}
	
	// iq_pass
	$Key = $szPreTag.'iq_pass';
	if(!isset($_POST[$Key]) || empty($_POST[$Key])) {
		$szErr = 'Введите желаемый пароль';
		return;
	}
	$DataValuesArr['iq_pass'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_pass'])) {
		$szErr = 'Введите желаемый пароль';
		return;
	}
	
	// iq_repass
	$Key = $szPreTag.'iq_repass';
	if(!isset($_POST[$Key]) || empty($_POST[$Key])) {
		$szErr = 'Введите повтор пароля';
		return;
	}
	$DataValuesArr['iq_repass'] = htmlspecialchars(trim($_POST[$Key]));
	if(empty($DataValuesArr['iq_repass'])) {
		$szErr = 'Введите повтор пароля';
		return;
	}
	
	if($DataValuesArr['iq_pass'] != $DataValuesArr['iq_repass']) {
		$szErr = 'Пароли не совпадают';
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

$bSkip = false;
foreach($DefArr AS $key => $data) {
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
		$bSkip = true;
	}
	if($bSkip) {
		break;
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
			$bSkip = true;
		}
	}
	if($bSkip) {
		break;
	}
}
if($bSkip) { 
	return;
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
$service_regist = 'regist_try';
$bCaptcha = false;
$bCaptchaShow = false;
if(isset($iq_auth_settings_arr['regist_captcha_enable']) && (int)$iq_auth_settings_arr['regist_captcha_enable']) {
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
#########################
##### EMAIL CONFIRM #####
#########################
if(isset($iq_auth_settings_arr['email_confirm']) && (int)$iq_auth_settings_arr['email_confirm']) {
	if(isset($iq_auth_settings_arr['email_confirm_length']) && (int)$iq_auth_settings_arr['email_confirm_length']) {
		$iCodeLength = (int)$iq_auth_settings_arr['email_confirm_length'];
	} else {
		$iCodeLength = 6;
	}
	
	if(isset($iq_auth_settings_arr['email_confirm_tries_max'])) {
		$iTriesMax = (int)$iq_auth_settings_arr['email_confirm_tries_max'];
	} else {
		$iTriesMax = 5;
	}
	
	$szPH = '';
	for($i = 0; $i < $iCodeLength; $i++) {
		$szPH .= '0';
	}
	
	// Send code
	$DataValuesArr['iq_code'] = '';
	$Key = $szPreTag.'iq_code';
	if($AjaxRequest) {
		if(in_array($Key, $ArrayData) && isset($_POST[$Key])) {
			$DataValuesArr['iq_code'] = htmlspecialchars(trim($_POST[$Key]));
		}
	} else {
		if(isset($_POST[$Key]) && !empty($_POST[$Key])) {
			$DataValuesArr['iq_code'] = htmlspecialchars(trim($_POST[$Key]));
		}
	}
	
	$service = 'confirm_regist';
	$szSendMsg = '';
	$szInput = '
		<div class="iq_authorization_padd_5">
			<input type="text" id="'.$szPreTag.'iq_code" name="'.$szPreTag.'iq_code" class="iq_authorization_input iq_authorization_input_default iq_authorization_align_center iq_authorization_block_centered" maxlength="'.$iCodeLength.'" placeholder="'.$szPH.'">
		</div>
	';
	
	if($iTriesMax) {
		// Checking for simulated input attempts
		$iTriesCount = $cIQAuthClass->antifloodGetCount($service);
		if($iTriesCount >= $iTriesMax) {
			$DataValuesArr['iq_code'] = '';
			$cIQAuthClass->antifloodRemove($service);
			$cIQAuthClass->codeRemove($service, $DataValuesArr['iq_email']);
		}
	}
	
	$code_already = $cIQAuthClass->codeGetAlready($service, $DataValuesArr['iq_email']);
	if(!empty($DataValuesArr['iq_code'])) {
		// check validate code
		if($iTriesMax) {
			$iTriesCount++;
			$szTriesMsg = '. Попыток: ['.$iTriesCount.'/'.$iTriesMax.']';
		} else {
			$szTriesMsg = '';
		}
		if($code_already != $DataValuesArr['iq_code']) {
			if($iTriesMax) {
				// add trie
				$cIQAuthClass->antifloodAdd($service);
			}
			
			$szErr = '
				<div id="confirm_block" class="iq_authorization_align_center">
					<div class="iq_authorization_alert_block iq_authorization_err">
						Введенный код не верный'.$szTriesMsg.'
					</div>
					'.$szInput.'
				</div>
			';
			if($AjaxRequest) {
				die($szErr);
			}
			return;
		}
		$cIQAuthClass->codeRemove($service, $DataValuesArr['iq_email']);
	} else {
		// send code
		if($code_already) {
			$szSendMsg = 'Недавно сообщение с кодом подтверждения аккаунта было отправлено на «'.$DataValuesArr['iq_email'].'». Пожалуйста проверьте, возможно по ошибке оно попало в папку «Спам»';
		} else {
			$szSendMsg = 'На email «'.$DataValuesArr['iq_email'].'» был отправлен код подтверждения регистрации. Пожалуйста введите его в поле ввода. Сообщение по ошибке может попасть в папку «Спам», пожалуйста проверьте эту папку и убедитесь что сообщения там нет';
			
			$szCode = func_iq_authorization_gen_pass($iCodeLength);
			$result = $cIQAuthClass->codeSend($service, $DataValuesArr['iq_email'], $szCode);
		
			if(!$result) {
				$szMsg = 'Не удалось отправить код подтверждения регистрации. Обратитесь в поддержку';
				if($AjaxRequest) {
					die($szMsg);
				}
				$szErr = $szMsg;
				return;
			}
			
			$szTheme = 'Код потверждения регистрации';
			if(isset($iq_auth_settings_arr['email_confirm_message']) && (int)$iq_auth_settings_arr['email_confirm_message']) {
			} else {
				$iq_auth_settings_arr['email_confirm_message'] = 'Здравствуйте! Код подтверждения Вашего аккаунта: {CODE}. Введите его в поле ввода на странице регистрации';
			}
			$email_message = str_replace("{CODE}", $szCode, $iq_auth_settings_arr['email_confirm_message']);
	
			// send email
			$email = get_option('admin_email');
			$headers = 'From: '. $email . "\r\n" .
				'Reply-To: ' . $email . "\r\n";
		
			$result = wp_mail(
				trim($DataValuesArr['iq_email']),
				$szTheme,
				strip_tags($email_message), 
				$headers
			);
			
			if(!$result) {
				$szMsg = 'Не удалось отправить код подтверждения регистрации. Обратитесь в поддержку';
				if($AjaxRequest) {
					die($szMsg);
				}
				$szErr = $szMsg;
				return;
			}
		}
		
		$szErr = '
			<div id="confirm_block" class="iq_authorization_align_center">
				<div class="iq_authorization_li_head">
					'.$szSendMsg.'
				</div>
				'.$szInput.'
			</div>
		';
		if($AjaxRequest) {
			die($szErr);
		}
		return;
	}
}

$user_id = wp_create_user( $DataValuesArr['iq_login'], $DataValuesArr['iq_pass'], $DataValuesArr['iq_email'] );
if(is_integer($user_id)) {
	// Success
	$credentials = [
		'user_login' => $DataValuesArr['iq_login'],
		'user_password' => $DataValuesArr['iq_pass'],
		'rememberme' => true,
	];
	$signon = wp_signon($credentials, true);
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
					Вы успешно зарегистрировались
				</div>
			';
			die($szMsg);
		}
		wp_redirect($redirect_url);
		return;
	}
}

$AuthRegist = (array)$user_id;
if(isset($AuthRegist['errors'])) {
	foreach($AuthRegist['errors'] AS $key => $data) {
		$szErr = $data[0];
		break;
	}
}
if(empty($szErr)) {
	$szErr = 'Ошибка регистрации';
}
if($AjaxRequest) {
	die($szErr);
}
return;
?>