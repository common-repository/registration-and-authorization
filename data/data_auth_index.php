<?php
if(!defined('ABSPATH')) {
	$AjaxRequest = true;
} else {
	$AjaxRequest = false;
}

if($AjaxRequest) {
	if(!isset($_SERVER['HTTP_REFERER'])) {
		die();
	}
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	require_once WP_PLUGIN_DIR.'/iq_authorize/includes/inc_func.php';
} else {
	echo get_header();
}

$bPop = 0;
if($bPop) {
	$szPreTag = 'pop_';
} else {
	$szPreTag = '';
}

if(!isset($iq_auth_settings_arr)) {
	$iq_auth_settings_arr = func_iq_authorization_get_settings();
}

// POST
$szErr = '';
if(isset($_POST[$szPreTag.'iq_login'])) {
	include dirname( __FILE__ ).'/data_login_do.php';
}

// iq_auth_settings_arr
if(isset($iq_auth_settings_arr['regist_url'])) {
	$regist_page = $iq_auth_settings_arr['regist_url'];
} else {
	$regist_page = iq_authorization_REGIST_PAGE;
}

$FormArr = [];
// login
$FormArr['login'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_form_label_line">
			<div class="iq_authorization_form_dash_icon_block">
				<span class="icofont-ui-email iq_authorization_form_dash_icon"></span>
			</div>
			<div>
				<div class="iq_authorization_form_label_txt">
					Логин/Email
				</div>
				<input type="text" id="'.$szPreTag.'iq_login" name="'.$szPreTag.'iq_login" class="iq_authorization_login_input iq_authorization_login_input_default iq_authorization_full_width_b" placeholder="email@example.com" maxlength="32" value="" autocomplete="off">
			</div>
		</div>
	</li>
';

// pass
$FormArr['pass'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_form_label_line">
			<div class="iq_authorization_form_dash_icon_block">
				<span class="icofont-ui-lock iq_authorization_form_dash_icon"></span>
			</div>
			<div>
				<div class="iq_authorization_form_label_txt">
					Пароль
				</div>
				<input type="password" id="'.$szPreTag.'iq_pass" name="'.$szPreTag.'iq_pass" class="iq_authorization_login_input iq_authorization_login_input_default iq_authorization_full_width_b" placeholder="*******" maxlength="32" value="" autocomplete="off">
			</div>
		</div>
	</li>
';

// form_captcha
$FormArr['form_captcha'] = '
	<li class="iq_authorization_li">
		<div id="'.$szPreTag.'form_captcha">
		</div>
	</li>
';

// notice
$FormArr['notice'] = '
	<li class="iq_authorization_li">
		<div id="'.$szPreTag.'form_notice">
';
if(!empty($szErr)) {
	$FormArr['notice'] .= '
		<div class="iq_authorization_alert_block iq_authorization_err">'.$szErr.'</div>
	';	
}
// notice
$FormArr['notice'] .= '
		</div>
	</li>
';

// buttons
$FormArr['buttons'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_login_btn_block">
			<button type="submit" class="iq_authorization_login_btn iq_authorization_f3 iq_authorization_nowrap">
				Войти
			</button>
			<a href="/'.$regist_page.'" class="iq_authorization_login_btn iq_authorization_f1 iq_authorization_nowrap" onclick="RegistPage();return false;">
				Создать
			</a>
		</div>
	</li>
';

// logo
$FormArr['logo'] = '';
if(isset($iq_auth_settings_arr['logo_enable']) && (int)$iq_auth_settings_arr['logo_enable']) {
	if(isset($iq_auth_settings_arr['logo']) && !empty($iq_auth_settings_arr['logo'])) {
		if(file_exists(iq_authorization_CORE_IMAGE_DIR.'/'.$iq_auth_settings_arr['logo'])) {
			$logo = iq_authorization_CORE_IMAGE_URL.'/'.$iq_auth_settings_arr['logo'];
			$width = 200;
			if(isset($iq_auth_settings_arr['logo_width']) && (int)$iq_auth_settings_arr['logo_width']) {
				$width = (int)$iq_auth_settings_arr['logo_width'];
			}
			$FormArr['logo'] = '
				<div class="iq_authorization_login_head_logo">
					<img src="'.$logo.'" alt="'.get_bloginfo().'" width="'.$width.'">
				</div>
			';
		}
	}
}
include iq_authorization_TPL_DIR.'/front/iq_authorization_login_index.php';

if(!$AjaxRequest) {
	echo get_footer();
}
?>