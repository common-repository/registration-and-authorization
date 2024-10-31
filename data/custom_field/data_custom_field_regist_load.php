<?php
if(defined("ABSPATH")) {
	$AjaxRequest = false;
	if(!defined("iq_authorization_CORE_DIR")) { die(); }
} else {
	$AjaxRequest = true;
}

if($AjaxRequest) {
	if(!isset($_SERVER['HTTP_REFERER'])) { die(); }

	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	require_once WP_PLUGIN_DIR.'/iq_authorize/includes/inc_func.php';
	
	$bPop = false;
	if(isset($_POST['Pop'])) {
		$bPop = (int)$_POST['Pop'];
	}
	if($bPop) {
		$szPreTag = 'pop_';
	} else {
		$szPreTag = '';
	}
}

if(!isset($cIQAuthCustomFields)) {
	require_once iq_authorization_CORE_DIR.'/includes/inc_custom_fields.php';
	$cIQAuthCustomFields = new IQAuthCustomFields();
}

$szStr = '';
$cf_arr = $cIQAuthCustomFields->getCustomFields();
if($cf_arr) {
	foreach($cf_arr AS $data) {
		$data['id'] = (int)$data['id'];
		if(!$data['enable']) {
			continue;
		}
		
		// icon
		$szIcon = '';
		if($data['icon']) {
			$szIcon = '<span class="'.$data['icon'].' iq_authorization_form_dash_icon"></span>';
		}
		
		// params
		$params_arr = json_decode($data['params_json'], true);
				
		switch($data['type_field']) {
			case 'input:text': {
				$script = '';
				if($data['type_type'] == 'int') {
					$script = 'onkeyup="return IQAuthOnlyNum(this.id);"';
				}
				
				$szStr .= '
					<li class="iq_authorization_li">
						<div class="iq_authorization_form_label_line">
							<div class="iq_authorization_form_dash_icon_block">
								'.$szIcon.'
							</div>
							<div>
								<div class="iq_authorization_form_label_txt">
									'.$data['name'].'
								</div>
								<input type="text" id="'.$szPreTag.'iq_cf_'.$data['id'].'" name="'.$szPreTag.'iq_cf_'.$data['id'].'" class="iq_authorization_login_input iq_authorization_login_input_default iq_authorization_full_width_b" placeholder="'.$data['ph'].'" maxlength="'.$params_arr['length_max'].'" value="" autocomplete="off" '.$script.'>
							</div>
						</div>
					</li>
				';
				break;
			}
			case 'select': {
				
				break;
			}
		}
	}
}

echo $szStr;
?>