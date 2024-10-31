<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}

$iq_auth_settings_arr = func_iq_authorization_get_settings();
?>
<div class="iq_authorization_core_block">
    <div class="iq_authorization_list_block iq_authorization_hided">
        <h2 class="iq_authorization_head_block">
            <?=esc_html__('Login page', 'iq_authorization_system');?>
        </h2>
		
		<?php
		################
		##### POST #####
		################
		$szErr = '';
		$bErr = false;
		if(isset($_POST['form_save'])) {
			// login_title
			$iq_auth_settings_arr['login_title'] = '';
			if(isset($_POST['login_title'])) {
				$iq_auth_settings_arr['login_title'] = htmlspecialchars(trim($_POST['login_title']));
			}
			
			// login_url
			$iq_auth_settings_arr['login_url'] = iq_authorization_AUTH_PAGE;
			if(isset($_POST['login_url'])) {
				$login_url = htmlspecialchars(trim($_POST['login_url']));
				if($login_url != $iq_auth_settings_arr['login_url']) {
					$iq_auth_settings_arr['login_url'] = $login_url;
				}
			}
			
			// login_captcha_enable
			$iq_auth_settings_arr['login_captcha_enable'] = false;
			if(isset($_POST['login_captcha_enable']) && $_POST['login_captcha_enable'] == 'on') {
				$iq_auth_settings_arr['login_captcha_enable'] = true;
			}
			
			$bSave = func_iq_authorization_updateSettings($iq_auth_settings_arr);
			if(!$bSave) {
				$szErr = 'Произошла ошибка';
				$bErr = true;
			}
			
			if($bErr) {
				if(empty($szErr)) {
					$szErr = 'Произошла ошибка';
				}
				$szNoticeMsg = '
					<div class="iq_authorization_alert_block iq_authorization_err">
						'.$szErr.'
					</div>
				';
			} else {
				$szNoticeMsg = '
					<div class="iq_authorization_alert_block iq_authorization_ok">
						Настройки сохранены
					</div>
				';
			}
            if(!empty($szNoticeMsg)) {
                echo $szNoticeMsg;
            }
		}
		?>
		
		<div class="iq_authorization_alert_block iq_authorization_info">
			В данном разделе Вы можете выполнить настройки страницы авторизации
		</div>
		
		<?php
		$FormArr = [];
		$FormVal = [
			'login_title' => '',
			'login_url' => iq_authorization_AUTH_PAGE,
			'login_captcha_enable' => '',
		];
		if(isset($iq_auth_settings_arr['login_title'])) {
			$FormVal['login_title'] = $iq_auth_settings_arr['login_title'];
		}
		if(isset($iq_auth_settings_arr['login_url'])) {
			$FormVal['login_url'] = $iq_auth_settings_arr['login_url'];
		}
		if(isset($iq_auth_settings_arr['login_captcha_enable']) && (int)$iq_auth_settings_arr['login_captcha_enable']) {
			$FormVal['login_captcha_enable'] = 'checked';
		}

		// login_title
		$FormArr['login_title'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Заголовок страницы входа
				</div>
				<input type="text" id="login_title" name="login_title" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w500" maxlength="64" placeholder="Вход" value="'.$FormVal['login_title'].'">
			</li>
		';

		// login_url
		$FormArr['login_url'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					URL страницы входа
				</div>
				<input type="text" id="login_url" name="login_url" class="iq_authorization_input iq_authorization_input_default" maxlength="64" placeholder="" value="'.$FormVal['login_url'].'">
			</li>
		';

		// login_captcha_enable
		$FormArr['login_captcha_enable'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Вкл/Выкл Captcha
				</div>
				<input class="iq_authorization_tgl iq_authorization_tgl_ios" id="login_captcha_enable" name="login_captcha_enable" type="checkbox" '.$FormVal['login_captcha_enable'].'>
				<label class="iq_authorization_tgl_btn" for="login_captcha_enable"></label>
			</li>
		';
		?>

		<form method="POST">
			<input type="hidden" name="form_save" value="1">
			<ul class="iq_authorization_ulcl">
				<?=$FormArr['login_title'];?>
				<?=$FormArr['login_url'];?>
				<?=$FormArr['login_captcha_enable'];?>
				
				<button type="submit" class="iq_authorization_button_light_blue iq_authorization_button_margin">
					Сохранить
				</button>
			</ul>
		</form>
		
	</div>
</div>