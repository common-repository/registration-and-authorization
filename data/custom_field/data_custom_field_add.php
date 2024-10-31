<?php
if(!isset($_SERVER['HTTP_REFERER'])) { die(); }

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
if (!function_exists('is_plugin_active')) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
require_once WP_PLUGIN_DIR.'/iq_authorize/includes/inc_func.php';

$iPostItemID = 0;
if(isset($_POST['ItemID'])) {
	$iPostItemID = (int)$_POST['ItemID'];
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

###############
##### POP #####
###############
$bPop = true;
if(isset($_POST['Pop'])) {
	$bPop = (int)$_POST['Pop'];
}
if($bPop) {
	$szPreTag = 'pop_';
} else {
	$szPreTag = '';
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

if($iPostItemID) {
	// edit
	$FormVal = [
		'head' => esc_html__('Edit custom field', 'iq_authorize'),
		'button' => esc_html__('Save', 'iq_authorize'),
		'type' => $ItemInfo['type_id'],
		'name' => $ItemInfo['name'],
		'ph' => $ItemInfo['ph'],
		'icon' => $ItemInfo['icon'],
		'important_check' => '',
		'enable_check' => '',
	];
	if($ItemInfo['important']) {
		$FormVal['important_check'] = 'checked';
	}
	if($ItemInfo['enable']) {
		$FormVal['enable_check'] = 'checked';
	}
} else {
	// create
	$FormVal = [
		'head' => esc_html__('Add custom field', 'iq_authorize'),
		'button' => esc_html__('Create', 'iq_authorize'),
		'type' => 0,
		'name' => '',
		'ph' => '',
		'icon' => '',
		'important_check' => '',
		'enable' => 'checked',
	];
}

$iq_custom_fields_types = $cIQAuthCustomFields->getTypes();

// types
$FormArr['types'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_li_head">
			Выберите тип поля
		</div>
	
';
if(empty($iq_custom_fields_types)) {
	$FormArr['types'] .= '
		<div class="iq_authorization_alert_block iq_authorization_err">
			Доступные типы не найдены в базе данных
		</div>
	';
} else {
	$FormArr['types'] .= '
		<select id="'.$szPreTag.'cf_type" class="iq_authorization_select iq_authorization_select_default" onchange="IQAuthCustomFieldTypeChange('.(int)$bPop.');">
			<option value="0">
				- Выберите тип поля -
			</option>
	';
	foreach($iq_custom_fields_types AS $data) {
		if($FormVal['type'] == (int)$data['id']) {
			$szDef = 'selected';
		} else {
			$szDef = '';
		}
		$FormArr['types'] .= '
			<option value="'.(int)$data['id'].'" '.$szDef.'>
				'.esc_html__($data['name'], 'iq_authorize').'
			</option>
		';
	}
	$FormArr['types'] .= '
		</select>
	';
}
$FormArr['types'] .= '
	</li>
';

// name
$FormArr['name'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_li_head">
			Введите название поля
		</div>
		<input type="text" id="'.$szPreTag.'cf_name" name="'.$szPreTag.'cf_name" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b" maxlength="64" placeholder="Название поля" value="'.$FormVal['name'].'">
	</li>
';

// ph
$FormArr['ph'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_li_head">
			Подсказка поля (placeholder)
		</div>
		<input type="text" id="'.$szPreTag.'cf_ph" name="'.$szPreTag.'cf_ph" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b" maxlength="64" placeholder="Пример текста" value="'.$FormVal['ph'].'">
	</li>
';

// icon
$FormArr['icon'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_li_head">
			Введите код иконки
		</div>
		<input type="text" id="'.$szPreTag.'cf_icon" name="'.$szPreTag.'cf_icon" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b" maxlength="64" placeholder="dashicons-welcome-view-site" value="'.$FormVal['icon'].'">
		<div class="iq_authorization_li_about">
			Коды иконок доступны по ссылке: https://developer.wordpress.org/resource/dashicons
		</div>
	</li>
';

// important
$FormArr['important'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_li_head">
			Обязательное поле?
		</div>
		<input class="iq_authorization_tgl iq_authorization_tgl_ios" id="'.$szPreTag.'cf_important" name="'.$szPreTag.'cf_important" type="checkbox" '.$FormVal['important_check'].'>
		<label class="iq_authorization_tgl_btn" for="'.$szPreTag.'cf_important"></label>
	</li>
';

// enable
$FormArr['enable'] = '
	<li class="iq_authorization_li">
		<div class="iq_authorization_li_head">
			Включено поле?
		</div>
		<input class="iq_authorization_tgl iq_authorization_tgl_ios" id="'.$szPreTag.'cf_enable" name="'.$szPreTag.'cf_enable" type="checkbox" '.$FormVal['enable_check'].'>
		<label class="iq_authorization_tgl_btn" for="'.$szPreTag.'cf_enable"></label>
	</li>
';

// cf_notice_block
$FormArr['cf_notice_block'] = '
	<li class="iq_authorization_li">
		<div id="'.$szPreTag.'block_cf_type_change" class="iq_authorization_mt_10"></div>
		<div id="'.$szPreTag.'cf_notice_block"></div>
	</li>
';

// buttons
$FormArr['buttons'] = '
	<li class="pop_li iq_authorization_center">
		<button class="iq_authorization_button_light_green iq_authorization_button_margin" onclick="IQAuthCustomFieldAddDo('.(int)$bPop.', '.(int)$iPostItemID.'); return false;">
			'.$FormVal['button'].'
		</button>
	</li>
';

$CArr = [
	'pop' => (int)$bPop,
	'item_id' => $iPostItemID,
];
$JsonData = json_encode($CArr);
$JsonDataP = func_iq_authorization_in_protect($JsonData);
$SignData = func_iq_authorization_get_sign_json_array_protect($JsonDataP);

$iMaxWidth = 700;
?>
<div id="pop">
	<input type="hidden" id="<?=$szPreTag;?>json_data" value="<?=$JsonDataP;?>">
	<input type="hidden" id="<?=$szPreTag;?>sign_data" value="<?=$SignData;?>">
	
	<div class="pop_overlay"></div>
	<div class="pop_modal pop_effect" id="pop_modal">
		<div class="pop_window">
			<div id="pop_pos" class="pop_pos">
				<div id="pop_window" class="pop_main" style="max-width:<?=$iMaxWidth;?>px;">
					<div class="pop_head">
						<div class="pop_head_txt">
							<?=$FormVal['head'];?>
						</div>	
						<button class="pop_close_button" onclick="PopClose();" data-content="<?=esc_html__('Close', 'iq_authorize');?>">
							<span class="dashicons dashicons-no"></span>
						</button>
					</div>
					<div id="pop_content" class="pop_body">
						<?php
						// echo '<pre>' . print_r($ItemInfo, true) . '</pre>';
						?>
						<ul id="<?=$szPreTag;?>form_data" class="pop_ul">
							<?=$FormArr['types'];?>
							<?=$FormArr['name'];?>
							<?=$FormArr['ph'];?>
							<?=$FormArr['icon'];?>
							<?=$FormArr['important'];?>
							<?=$FormArr['enable'];?>
							<?=$FormArr['cf_notice_block'];?>
							<?=$FormArr['buttons'];?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>