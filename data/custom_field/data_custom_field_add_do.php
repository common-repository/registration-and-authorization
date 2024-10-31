<?php
if(!isset($_SERVER['HTTP_REFERER'])) { die(); }

if(!isset($_POST['Values'])) {
	die();
}

if(!isset($_POST['JsonData']) ||
!isset($_POST['SignData'])) {
	die();
}

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
if (!function_exists('is_plugin_active')) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
require_once WP_PLUGIN_DIR.'/iq_authorize/includes/inc_func.php';

// Protect
$JsonProtect = $_POST['JsonData'];
$SignCheck = func_iq_authorization_get_sign_json_array_protect($JsonProtect);
$szPostSign = trim($_POST['SignData']);
if($szPostSign != $SignCheck) { die(); }
$JsonVal = func_iq_authorization_out_protect($JsonProtect);

########################
##### DATA PROTECT #####
########################
$CArr = json_decode($JsonVal, true);
if(empty($CArr)) {
	return false;
}

// item_id
if(!isset($CArr['item_id'])) {
	die();
}
$CArr['item_id'] = (int)$CArr['item_id'];
$iPostItemID = (int)$CArr['item_id'];

###############
##### POP #####
###############
if(!isset($CArr['pop'])) {
	die();
}
$CArr['pop'] = (int)$CArr['pop'];

if($CArr['pop']) {
	$szPreTag = 'pop_';
} else {
	$szPreTag = '';
}

##################
##### ACCESS #####
##################
if ( !is_user_logged_in() ) {
	$szMsg = esc_html__('Authorization required', 'iq_authorize');
	die($szMsg);
}

$cur_user_id = get_current_user_id();
if($cur_user_id <= 0) {
	$szMsg = esc_html__('Authorization required', 'iq_authorize');
	die($szMsg);
}
$user_obj = get_userdata( $cur_user_id );

if(!$user_obj) {
	wp_logout();
	$szMsg = esc_html__('Authorization required', 'iq_authorize');
	die($szMsg);
}
$roles_arr = (array)$user_obj->roles;

$need_role = 'administrator';
if(!in_array($need_role, $roles_arr)) {
	$szMsg = esc_html__('Access denied', 'iq_authorize');
	die($szMsg);
}


#######################
##### DATA VALUES #####
#######################
$DataValuesArr = array();
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

if(!isset($cIQAuthCustomFields)) {
	require_once iq_authorization_CORE_DIR.'/includes/inc_custom_fields.php';
	$cIQAuthCustomFields = new IQAuthCustomFields();
}

// cf_name
$Key = $szPreTag.'cf_name';
if(!in_array($Key, $ArrayData) || empty($_POST[$Key])) {
	$szMsg = 'Введите название поля';
	die($szMsg);
}
$DataValuesArr['name'] = htmlspecialchars(trim($_POST[$Key]));
if(empty($DataValuesArr['name'])) {
	$szMsg = 'Введите название поля';
	die($szMsg);
}

// cf_ph
$Key = $szPreTag.'cf_ph';
$DataValuesArr['ph'] = '';
if(in_array($Key, $ArrayData) && !empty($_POST[$Key])) {
	$DataValuesArr['ph'] = htmlspecialchars(trim($_POST[$Key]));
}

// cf_icon
$Key = $szPreTag.'cf_icon';
$DataValuesArr['icon'] = '';
if(in_array($Key, $ArrayData) && !empty($_POST[$Key])) {
	$DataValuesArr['icon'] = htmlspecialchars(trim($_POST[$Key]));
	if(strpos($DataValuesArr['icon'], 'icofont') === false) {
		$szMsg = 'Иконка указана не верно. Код иконки должен содержать icofont';
		die($szMsg);
	}
}

// cf_important
$Key = $szPreTag.'cf_important';
$DataValuesArr['important'] = 0;
if(in_array($Key, $ArrayData) && $_POST[$Key] == 'true') {
	$DataValuesArr['important'] = 1;
}

// cf_enable
$Key = $szPreTag.'cf_enable';
$DataValuesArr['enable'] = 1;
if(in_array($Key, $ArrayData) && $_POST[$Key] == 'false') {
	$DataValuesArr['enable'] = 0;
}

// cf_type
$Key = $szPreTag.'cf_type';
if(!in_array($Key, $ArrayData) || (int)$_POST[$Key] <= 0) {
	$szMsg = 'Выберите тип поля';
	die($szMsg);
}
$DataValuesArr['type'] = (int)$_POST[$Key];

$Params = [];
$iq_custom_fields_types = $cIQAuthCustomFields->getTypes();
if($iq_custom_fields_types) {
	foreach($iq_custom_fields_types AS $key => $data) {
		if($DataValuesArr['type'] == (int)$data['id']) {
			$Params = $data;
			break;
		}
	}
}
if(empty($Params)) {
	$szMsg = 'Неизвестный тип поля';
	die($szMsg);
}

$DataValuesArr['type_field'] = $Params['field'];
switch($Params['field']) {
	case 'input:text': {
		// cf_length_min
		$Key = $szPreTag.'cf_length_min';
		if(!in_array($Key, $ArrayData) || (int)$_POST[$Key] <= 0) {
			$szMsg = 'Минимальная длина должна быть больше нуля';
			die($szMsg);
		}
		$DataValuesArr['length_min'] = (int)$_POST[$Key];
		
		// cf_length_max
		$Key = $szPreTag.'cf_length_max';
		if(!in_array($Key, $ArrayData) || (int)$_POST[$Key] <= 0) {
			$szMsg = 'Максимальная длина должна быть больше нуля';
			die($szMsg);
		}
		$DataValuesArr['length_max'] = (int)$_POST[$Key];
		
		if($DataValuesArr['length_max'] <= $DataValuesArr['length_min']) {
			$szMsg = 'Максимальная длина должна быть больше минимальной';
			die($szMsg);
		}
		
		// cf_regex
		$Key = $szPreTag.'cf_regex';
		$DataValuesArr['regex'] = '';
		if(in_array($Key, $ArrayData) && !empty(trim($_POST[$Key]))) {
			$DataValuesArr['regex'] = htmlspecialchars(trim($_POST[$Key]));
		}
		
		// cf_regex_txt
		$Key = $szPreTag.'cf_regex_txt';
		$DataValuesArr['regex_txt'] = '';
		if(in_array($Key, $ArrayData) && !empty(trim($_POST[$Key]))) {
			$DataValuesArr['regex_txt'] = htmlspecialchars(trim($_POST[$Key]));
		}
		break;
	}
	
	case 'select': {
		// cf_select_name
		$Key = $szPreTag.'cf_select_name';
		if(!in_array($Key, $ArrayData) || empty($_POST[$Key])) {
			$szMsg = 'Введите название поля выбора';
			die($szMsg);
		}
		$DataValuesArr['select_name'] = htmlspecialchars(trim($_POST[$Key]));
		if(empty($DataValuesArr['select_name'])) {
			$szMsg = 'Введите название поля выбора';
			die($szMsg);
		}
		
		// options
		$DataValuesArr['select_options_arr'] = [];
		if(!isset($_POST['Options'])) {
			die();
		}
		$PostOptions = htmlspecialchars(trim($_POST['Options']));
		$Exp = explode(',', $PostOptions);
		for($i = 0; $i < count($Exp); $i++) {
			$TempParam = trim($Exp[$i]);
			if(empty($TempParam)) { continue; }
			$DataValuesArr['select_options_arr'][] = $TempParam;
		}
		
		break;
	}
}

$result = $cIQAuthCustomFields->add($DataValuesArr, $iPostItemID);
if(!$result) {
	$szMsg = 'Произошла ошибка при попытке создать поле';
	die($szMsg);
}

$szMsg = 'Поле «'.$DataValuesArr['name'].'» успешно создано';
?>
<div id="success">
	<?=$szMsg;?>
</div>