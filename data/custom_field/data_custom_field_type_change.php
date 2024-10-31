<?php
if(!isset($_SERVER['HTTP_REFERER'])) { die(); }

if(!isset($_POST['Val'])) {
	die();
}
$iPostVal = (int)$_POST['Val'];
if($iPostVal <= 0) {
	return false;
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
$bPop = (int)$CArr['pop'];

if($CArr['pop']) {
	$szPreTag = 'pop_';
} else {
	$szPreTag = '';
}

##################
##### ACCESS #####
##################
if ( !is_user_logged_in() ) {
	die();
}

$cur_user_id = get_current_user_id();
if($cur_user_id <= 0) {
	die();
}
$user_obj = get_userdata( $cur_user_id );

if(!$user_obj) {
	wp_logout();
	die();
}
$roles_arr = (array)$user_obj->roles;

$need_role = 'administrator';
if(!in_array($need_role, $roles_arr)) {
	die();
}

if(!isset($cIQAuthCustomFields)) {
	require_once iq_authorization_CORE_DIR.'/includes/inc_custom_fields.php';
	$cIQAuthCustomFields = new IQAuthCustomFields();
}

if($iPostItemID) {
	$SQL_Search = "
		AND
			a.`id` = '".$iPostItemID."'
	";
	$results = $cIQAuthCustomFields->getCustomFields([], $SQL_Search);
	$ItemInfo = [];
	if($results) {
		foreach($results AS $data) {
			$ItemInfo = $data;
			break;
		}
	}
	if(!$ItemInfo) {
		$iPostItemID = 0;
	}
}

$iq_custom_fields_types = $cIQAuthCustomFields->getTypes();

$Params = [];
if($iq_custom_fields_types) {
	foreach($iq_custom_fields_types AS $key => $data) {
		if($iPostVal == (int)$data['id']) {
			$Params = $data;
			break;
		}
	}
}
if(empty($Params)) {
	$szMsg = '
		<div class="iq_authorization_alert_block iq_authorization_err">
			Неизвестный тип поля
		</div>
	';
	die($szMsg);
}

$szStr = '';
$szStr .= '
	<ul class="pop_ul">

';
switch($Params['field']) {
	case 'input:text': {
		if($iPostItemID) {
			$params_arr = json_decode($ItemInfo['params_json'], true);
			// edit
			$FormVal = [
				'length_min' => $params_arr['length_min'],
				'length_max' => $params_arr['length_max'],
				'regex' => $params_arr['regex'],
				'regex_txt' => $params_arr['regex_txt'],
			];
		} else {
			// create
			$FormVal = [
				'length_min' => 2,
				'length_max' => 32,
				'regex' => '',
				'regex_txt' => '',
			];
		}
		
		$szStr .= '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Допустимая длина строки
				</div>
				<div class="iq_authorization_flexbox_st iq_authorization_flexbox_yc iq_authorization_flex_gap_2">
					<div>
						<input type="text" id="'.$szPreTag.'cf_length_min" name="'.$szPreTag.'cf_length_min" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="5" placeholder="от" value="'.$FormVal['length_min'].'" onkeyup="return IQAuthOnlyNum(this.id);">
					</div>
					<div>
						-
					</div>
					<div>
						<input type="text" id="'.$szPreTag.'cf_length_max" name="'.$szPreTag.'cf_length_max" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="5" placeholder="до" value="'.$FormVal['length_max'].'" onkeyup="return IQAuthOnlyNum(this.id);">
					</div>
				</div>
			</li>
		';
		
		$szStr .= '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					REGEX валидация для поля
				</div>
				<input type="text" id="'.$szPreTag.'cf_regex" name="'.$szPreTag.'cf_regex" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w700" maxlength="128" placeholder="/^[A-Za-z0-9_\-\.]+$/" value="'.$FormVal['regex'].'">
			</li>
		';
		
		$szStr .= '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Сообщение для пользователя если валидация REGEX не пройдена
				</div>
				<textarea id="'.$szPreTag.'cf_regex_txt" name="'.$szPreTag.'cf_regex_txt" class="iq_authorization_textarea iq_authorization_full_width_b iq_authorization_max_w700" maxlength="300" rows="3">'.$FormVal['regex_txt'].'</textarea>
			</li>
		';
		break;
	}
	
	case 'select': {
		// cf_select_name
		if($iPostItemID) {
			$params_arr = json_decode($ItemInfo['params_json'], true);
			// edit
			$FormVal = [
				'select_name' => $params_arr['select_name'],
			];
		} else {
			// create
			$FormVal = [
				'select_name' => '',
			];
		}
		
		
		$szStr .= '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Название поля выбора
				</div>
				<input type="text" id="'.$szPreTag.'cf_select_name" name="'.$szPreTag.'cf_select_name" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w700" maxlength="128" placeholder="Выберите опцию" value="'.$FormVal['select_name'].'">
			</li>
		';
		$szStr .= '
			<li class="iq_authorization_li">
				<div id="'.$szPreTag.'block_options" class="'.$szPreTag.'block_options iq_authorization_mb_20">
		';
				
		if($iPostItemID) {
			if(isset($params_arr['options_arr']) && !empty($params_arr['options_arr'])) {
				for($i = 0; $i < count($params_arr['options_arr']); $i++) {
					$szStr .= '
						<div class="iq_authorization_mt_5"><input type="text" name="'.$szPreTag.'cf_option" class="option_class iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w300" maxlength="128" placeholder="New option" value="'.$params_arr['options_arr'][$i].'"></div>
					';
				}
			}
		}
		
		$szStr .= '	
				</div>
				<a href="#" class="iq_authorization_button_pop iq_authorization_button_margin" onclick="IQAuthCustomFieldAddOption('.(int)$bPop.');return false;">
					Добавить опцию выбора
				</a>
			</li>
		';
		
		break;
	}
}
$szStr .= '
	</ul>
';
echo $szStr;