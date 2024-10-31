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
            <?=esc_html__('Registration page', 'iq_authorization_system');?>
        </h2>
		
		<?php
		################
		##### POST #####
		################
		$szErr = '';
		$bErr = false;
		if(isset($_POST['form_save'])) {
			// regist_title
			$iq_auth_settings_arr['regist_title'] = '';
			if(isset($_POST['regist_title'])) {
				$iq_auth_settings_arr['regist_title'] = htmlspecialchars(trim($_POST['regist_title']));
			}
			
			// regist_url
			$iq_auth_settings_arr['regist_url'] = iq_authorization_REGIST_PAGE;
			if(isset($_POST['regist_url'])) {
				$regist_url = htmlspecialchars(trim($_POST['regist_url']));
				if($regist_url != $iq_auth_settings_arr['regist_url']) {
					$iq_auth_settings_arr['regist_url'] = $regist_url;
				}
			}
			
			// regist_captcha_enable
			$iq_auth_settings_arr['regist_captcha_enable'] = false;
			if(isset($_POST['regist_captcha_enable']) && $_POST['regist_captcha_enable'] == 'on') {
				$iq_auth_settings_arr['regist_captcha_enable'] = true;
			}
			
			// email_confirm
			$iq_auth_settings_arr['email_confirm'] = false;
			if(isset($_POST['email_confirm']) && $_POST['email_confirm'] == 'on') {
				$iq_auth_settings_arr['email_confirm'] = true;
			}
			
			// email_confirm_length
			$iq_auth_settings_arr['email_confirm_length'] = 6;
			if(isset($_POST['email_confirm_length'])) {
				$iq_auth_settings_arr['email_confirm_length'] = (int)$_POST['email_confirm_length'];
			}
			
			// email_confirm_tries_max
			$iq_auth_settings_arr['email_confirm_tries_max'] = 5;
			if(isset($_POST['email_confirm_tries_max'])) {
				$iq_auth_settings_arr['email_confirm_tries_max'] = (int)$_POST['email_confirm_tries_max'];
			}
			
			// email_confirm_message
			$iq_auth_settings_arr['email_confirm_message'] = '';
			if(isset($_POST['email_confirm_message'])) {
				$iq_auth_settings_arr['email_confirm_message'] = $_POST['email_confirm_message'];
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
			В данном разделе Вы можете выполнить настройки страницы регистрации
		</div>
		
		<?php
		$FormArr = [];
		$FormVal = [
			'regist_title' => '',
			'regist_url' => iq_authorization_REGIST_PAGE,
			'regist_captcha_enable' => '',
			'email_confirm' => '',
			'email_confirm_message' => 'Здравствуйте! Код подтверждения Вашего аккаунта: {CODE}
Введите его в поле ввода на странице регистрации',
			'email_confirm_length' => 6,
			'email_confirm_tries_max' => 5,
		];
		if(isset($iq_auth_settings_arr['regist_title'])) {
			$FormVal['regist_title'] = $iq_auth_settings_arr['regist_title'];
		}
		if(isset($iq_auth_settings_arr['regist_url'])) {
			$FormVal['regist_url'] = $iq_auth_settings_arr['regist_url'];
		}
		if(isset($iq_auth_settings_arr['regist_captcha_enable']) && (int)$iq_auth_settings_arr['regist_captcha_enable']) {
			$FormVal['regist_captcha_enable'] = 'checked';
		}
		if(isset($iq_auth_settings_arr['email_confirm']) && (int)$iq_auth_settings_arr['email_confirm']) {
			$FormVal['email_confirm'] = 'checked';
		}
		if(isset($iq_auth_settings_arr['email_confirm_length'])) {
			$FormVal['email_confirm_length'] = (int)$iq_auth_settings_arr['email_confirm_length'];
		}
		if(isset($iq_auth_settings_arr['email_confirm_tries_max'])) {
			$FormVal['email_confirm_tries_max'] = (int)$iq_auth_settings_arr['email_confirm_tries_max'];
		}
		if(isset($iq_auth_settings_arr['email_confirm_message'])) {
			$FormVal['email_confirm_message'] = $iq_auth_settings_arr['email_confirm_message'];
		}

		// regist_title
		$FormArr['regist_title'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Заголовок страницы регистрации
				</div>
				<input type="text" id="regist_title" name="regist_title" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w500" maxlength="64" placeholder="Регистрация" value="'.$FormVal['regist_title'].'">
			</li>
		';

		// regist_url
		$FormArr['regist_url'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					URL страницы регистрации
				</div>
				<input type="text" id="regist_url" name="regist_url" class="iq_authorization_input iq_authorization_input_default" maxlength="64" placeholder="" value="'.$FormVal['regist_url'].'">
			</li>
		';

		// regist_captcha_enable
		$FormArr['regist_captcha_enable'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Вкл/Выкл Captcha
				</div>
				<input class="iq_authorization_tgl iq_authorization_tgl_ios" id="regist_captcha_enable" name="regist_captcha_enable" type="checkbox" '.$FormVal['regist_captcha_enable'].'>
				<label class="iq_authorization_tgl_btn" for="regist_captcha_enable"></label>
			</li>
		';
		
		// email_confirm
		$FormArr['email_confirm'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Подтверждение Email при регистрации
				</div>
				<input class="iq_authorization_tgl iq_authorization_tgl_ios" id="email_confirm" name="email_confirm" type="checkbox" '.$FormVal['email_confirm'].'>
				<label class="iq_authorization_tgl_btn" for="email_confirm"></label>
			</li>
		';
		
		// email_confirm_length
		$FormArr['email_confirm_length'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Длина кода подтверждения
				</div>
				<input type="text" id="email_confirm_length" name="email_confirm_length" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="10" placeholder="6" value="'.$FormVal['email_confirm_length'].'" onkeyup="return IQAuthOnlyNum(this.id);">
			</li>
		';
		
		// email_confirm_message
		$FormArr['email_confirm_message'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Письмо отправленное пользователю с кодом активации аккаунта
				</div>
				<textarea id="email_confirm_message" name="email_confirm_message" class="iq_authorization_textarea iq_authorization_full_width_b iq_authorization_max_w700" maxlength="1000" rows="4">'.$FormVal['email_confirm_message'].'</textarea>
				<div class="iq_authorization_li_about">
					Используйте тег {CODE} в поле для отображения кода активации
				</div>
			</li>
		';
		
		// email_confirm_tries_max
		$FormArr['email_confirm_tries_max'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Максимальное количество ввода неправильного кода подтверждения
				</div>
				<input type="text" id="email_confirm_tries_max" name="email_confirm_tries_max" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="5" placeholder="6" value="'.$FormVal['email_confirm_tries_max'].'" onkeyup="return IQAuthOnlyNum(this.id);">
			</li>
		';
		?>

		<form method="POST">
			<input type="hidden" name="form_save" value="1">
			<ul class="iq_authorization_ulcl">
				<?=$FormArr['regist_title'];?>
				<?=$FormArr['regist_url'];?>
				<?=$FormArr['regist_captcha_enable'];?>
				<?=$FormArr['email_confirm'];?>
				<?=$FormArr['email_confirm_length'];?>
				<?=$FormArr['email_confirm_tries_max'];?>
				<?=$FormArr['email_confirm_message'];?>
				
				<button type="submit" class="iq_authorization_button_light_blue iq_authorization_button_margin">
					Сохранить
				</button>
			</ul>
		</form>
		
	</div>
</div>