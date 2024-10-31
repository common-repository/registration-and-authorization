<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}

$iq_auth_settings_arr = func_iq_authorization_get_settings();

// def inputs
$DefArr['login'] = [
	'name' => 'Логин',
	'regex' => '/^(?=.*[A-Za-zА-Яа-яёґєії0-9])[A-Za-zА-Яа-яёґєії][A-Za-zА-Яа-яёґєії\d .-]{0,19}$/',
	'min' => 2,
	'max' => 64,
	'regex_text' => 'Логин может содержать только буквы и цифры + (максимум 1 пробел, знаки ".", "-"). Первый знак должен быть буквой',
];
$DefArr['email'] = [
	'name' => 'Email',
	'regex' => '/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/',
	'min' => 2,
	'max' => 64,
	'regex_text' => '',
];
$DefArr['pass'] = [
	'name' => 'Пароль',
	'regex' => '/^[A-Za-z0-9_\-\.]+$/',
	'min' => 2,
	'max' => 64,
	'regex_text' => 'Пароль может содержать только буквы латинского алфавита и/или цифры (доступны знаки: «_», «-» и «.»)',
];

?>
<div class="iq_authorization_core_block">
    <div class="iq_authorization_list_block iq_authorization_hided">
        <h2 class="iq_authorization_head_block">
            Общие настройки
        </h2>
		<?php
		################
		##### POST #####
		################
		$szErr = '';
		$bErr = false;
		if(isset($_POST['form_save'])) {
			// auth_redirect
			$iq_auth_settings_arr['auth_redirect'] = '';
			if(isset($_POST['auth_redirect'])) {
				$iq_auth_settings_arr['auth_redirect'] = htmlspecialchars(trim($_POST['auth_redirect']));
			}
			// captcha_pub
			$iq_auth_settings_arr['captcha_pub'] = '';
			if(isset($_POST['captcha_pub'])) {
				$iq_auth_settings_arr['captcha_pub'] = htmlspecialchars(trim($_POST['captcha_pub']));
			}
			// captcha_secret
			$iq_auth_settings_arr['captcha_secret'] = '';
			if(isset($_POST['captcha_secret'])) {
				$iq_auth_settings_arr['captcha_secret'] = htmlspecialchars(trim($_POST['captcha_secret']));
			}
			// captcha_limit_free
			$iq_auth_settings_arr['captcha_limit_free'] = 3;
			if(isset($_POST['captcha_limit_free'])) {
				$iq_auth_settings_arr['captcha_limit_free'] = (int)$_POST['captcha_limit_free'];
			}
			// logo_width
			$iq_auth_settings_arr['logo_width'] = 200;
			if(isset($_POST['logo_width'])) {
				$iq_auth_settings_arr['logo_width'] = (int)$_POST['logo_width'];
			}
			
			// logo_enable
			$iq_auth_settings_arr['logo_enable'] = false;
			if(isset($_POST['logo_enable']) && $_POST['logo_enable'] == 'on') {
				$iq_auth_settings_arr['logo_enable'] = true;
			}
			
			// length
			foreach($DefArr AS $key => $data) {
				$iMin = $data['min'];
				$iMax = $data['max'];
				if(isset($_POST[$key.'_length_min'])) {
					$iMin = (int)$_POST[$key.'_length_min'];
				}
				if(isset($_POST[$key.'_length_max'])) {
					$iMax = (int)$_POST[$key.'_length_max'];
				}
				
				if($iMax < $iMin) {
					$szErr = 'Максимальная длина поля «'.$data['name'].'» не может быть меньше минимальной длины';
					$bErr = true;
				}
				
				if($bErr) {
					break;
				}
				
				$iq_auth_settings_arr[$key.'_length'] = $iMin.':'.$iMax;
				
				$iq_auth_settings_arr[$key.'_regex'] = $data['regex'];
				if(isset($_POST[$key.'_regex'])) {
					$iq_auth_settings_arr[$key.'_regex'] = trim($_POST[$key.'_regex']);
				}
				
				// regex_text
				$iq_auth_settings_arr[$key.'_regex_text'] = $data['regex_text'];
				if(isset($_POST[$key.'_regex_text'])) {
					$iq_auth_settings_arr[$key.'_regex_text'] = trim($_POST[$key.'_regex_text']);
				}
			}
			
			if(!$bErr) {
				// Logotype
				if(isset($_FILES['form_logo']) && !empty($_FILES['form_logo']) && !empty($_FILES['form_logo']['tmp_name'])) {
					$File = $_FILES['form_logo'];
					$AllowedExtsFormats = array("png", "svg");
					$FileFormat = explode('.', $File['name']);
					$FileFormat = array_pop($FileFormat);
					if(!in_array($FileFormat, $AllowedExtsFormats, true)) {
						$szErr = 'Логотип не был загружен. Поддерживаемые форматы логотипа: PNG, SVG';
						$bErr = true;
					} else {
						$FileType = $File['type'];
						if($FileType != 'image/png' && $FileType != 'image/svg+xml') {
							$szErr = 'Логотип не был загружен. Поддерживаемые форматы логотипа: PNG, SVG';
							$bErr = true;
						} else {
							$FileName = 'logo_'.date('dmYHis').'.'.$FileFormat;
							$DirOut = iq_authorization_CORE_IMAGE_DIR;
							if(!move_uploaded_file($File['tmp_name'], $DirOut.'/'.$FileName)) {
								$szErr = 'Не удалось сохранить логотип в каталог: '.$DirOut;
								$bErr = true;
							} else {
								if(!file_exists($DirOut.'/'.$FileName)) {
									$szErr = 'Не удалось сохранить логотип';
									$bErr = true;
								} else {
									$iq_auth_settings_arr['logo'] = $FileName;
								}
							}
						}
					}
				}
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
		
		
		##############
		$FormArr = [];
		$FormVal = [
			'auth_redirect' => '',
			'captcha_pub' => '',
			'captcha_secret' => '',
			'captcha_limit_free' => 3,
			'form_logo' => '',
			'logo_width' => 200,
			'logo_enable' => '',
		];
		
		foreach($DefArr AS $key => $data) {
			$FormVal[$key.'_length'] = $data['min'].':'.$data['max'];
		}
		
		if(isset($iq_auth_settings_arr['logo_enable']) && (int)$iq_auth_settings_arr['logo_enable']) {
			$FormVal['logo_enable'] = 'checked';
		}
		if(isset($iq_auth_settings_arr['auth_redirect'])) {
			$FormVal['auth_redirect'] = $iq_auth_settings_arr['auth_redirect'];
		}
		if(isset($iq_auth_settings_arr['captcha_pub'])) {
			$FormVal['captcha_pub'] = $iq_auth_settings_arr['captcha_pub'];
		}
		if(isset($iq_auth_settings_arr['captcha_secret'])) {
			$FormVal['captcha_secret'] = $iq_auth_settings_arr['captcha_secret'];
		}
		if(isset($iq_auth_settings_arr['captcha_limit_free'])) {
			$FormVal['captcha_limit_free'] = (int)$iq_auth_settings_arr['captcha_limit_free'];
		}
		if(isset($iq_auth_settings_arr['logo_width'])) {
			$FormVal['logo_width'] = (int)$iq_auth_settings_arr['logo_width'];
		}
		if(isset($iq_auth_settings_arr['form_logo'])) {
			// $FormVal['form_logo'] = $iq_auth_settings_arr['form_logo'];
		}
		
		foreach($DefArr AS $key => $data) {
			if(isset($iq_auth_settings_arr[$key.'_length'])) {
				$FormVal[$key.'_length'] = $iq_auth_settings_arr[$key.'_length'];
			}
			$Exp = explode(':', $FormVal[$key.'_length']);
			$FormVal[$key.'_length_min'] = (int)$Exp[0];
			$FormVal[$key.'_length_max'] = (int)$Exp[1];
			
			$FormVal[$key.'_regex'] = $data['regex'];
			if(isset($iq_auth_settings_arr[$key.'_regex'])) {
				$FormVal[$key.'_regex'] = stripcslashes($iq_auth_settings_arr[$key.'_regex']);
			}
			
			$FormVal[$key.'_regex_text'] = $data['regex_text'];
			if(isset($iq_auth_settings_arr[$key.'_regex_text'])) {
				$FormVal[$key.'_regex_text'] = stripcslashes($iq_auth_settings_arr[$key.'_regex_text']);
			}
		}

		// auth_redirect
		$FormArr['auth_redirect'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					URL перенаправления после авторизации
				</div>
				<div class="iq_authorization_flexbox_st iq_authorization_flexbox_yc iq_authorization_flex_gap_2">
					<div>
						'.((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER ['SERVER_NAME'].'/
					</div>
					<div>
						<input type="text" id="auth_redirect" name="auth_redirect" class="iq_authorization_input iq_authorization_input_default iq_authorization_padd_3 iq_authorization_full_width_b iq_authorization_max_w500" maxlength="250" placeholder="" value="'.$FormVal['auth_redirect'].'">
					</div>
				</div>
				<div class="iq_authorization_li_about">
					На данный URL пользователь будет автоматически перенаправлен после успешной авторизации или регистрации на сайте
				</div>
			</li>
		';

		// captcha_pub
		$FormArr['captcha_pub'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Публичный ключ Captcha
				</div>
				<input type="text" id="captcha_pub" name="captcha_pub" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w500" maxlength="255" placeholder="" value="'.$FormVal['captcha_pub'].'">
			</li>
		';

		// captcha_secret
		$FormArr['captcha_secret'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Секретный ключ Captcha
				</div>
				<input type="text" id="captcha_secret" name="captcha_secret" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w500" maxlength="255" placeholder="" value="'.$FormVal['captcha_secret'].'">
				<div class="iq_authorization_li_about">
					Captcha ключи Вы можете получить по ссылке https://www.google.com/recaptcha/admin/create
				</div>
			</li>
		';
		
		// captcha_limit_free
		$FormArr['captcha_limit_free'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_li_head">
					Попыток перед тем как CAPTCHA будет отображена (0 - всегда)
				</div>
				<input type="text" id="captcha_limit_free" name="captcha_limit_free" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="10" placeholder="3" value="'.$FormVal['captcha_limit_free'].'" onkeyup="return IQAuthOnlyNum(this.id);">
			</li>
		';
		
		// form_logo
		$logo_image = iq_authorization_CORE_IMAGE_URL.'/iq_auth_nologo.png';
		if(isset($iq_auth_settings_arr['logo'])) {
			$DirOut = iq_authorization_CORE_IMAGE_DIR;
			if(file_exists($DirOut.'/'.$iq_auth_settings_arr['logo'])) {
				$logo_image = iq_authorization_CORE_IMAGE_URL.'/'.$iq_auth_settings_arr['logo'];
			}
		}
		$FormArr['form_logo'] = '
			<li class="iq_authorization_li">
				<div class="iq_authorization_flexbox_tab iq_authorization_flex_gap">
					<div class="iq_authorization_flex_200 iq_authorization_padd_7 iq_authorization_info_txt">
						Логотип будет отображен на странице авторизации и регистрации пользователя<br>
						&bull; Нажмите на изображение чтобы обновить/загрузить логотип<br>
						&bull; Форматы логотипа: .PNG или .SVG<br>
						
						<div class="iq_authorization_mt_20">
							<div class="iq_authorization_li_head">
								Ширина логотипа
							</div>
							<input type="text" id="logo_width" name="logo_width" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w200 iq_authorization_align_center" maxlength="10" placeholder="3" value="'.$FormVal['logo_width'].'" onkeyup="return IQAuthOnlyNum(this.id);">
						</div>
						
						<div class="iq_authorization_mt_20">
							<div class="iq_authorization_li_head">
								Показывать логотип?
							</div>
							<input class="iq_authorization_tgl iq_authorization_tgl_ios" id="logo_enable" name="logo_enable" type="checkbox" '.$FormVal['logo_enable'].'>
							<label class="iq_authorization_tgl_btn" for="logo_enable"></label>
						</div>
					</div>
					<div class="iq_authorization_flex_200 iq_authorization_padd_7">
						<div class="iq_authorization_sdt_upload_re">
							<label for="form_logo" class="iq_authorization_filup_re full_width_b">
								<div id="icon_block" class=" pos_rel">
									<img src="'.$logo_image.'" id="icon_id_src" class="iq_authorization_sdt_img" alt="IQ Auth logo">
								</div>
								<input type="file" name="form_logo" id="form_logo" onchange="IQAuthPreviewImage(\'icon_id_src\', this);" data-value="0" accept=".png,.svg">
							</label>
						</div>
					</div>
				</div>
			</li>
		';

		// length
		foreach($DefArr AS $key => $data) {
			$FormArr[$key.'_length'] = '
				<li class="iq_authorization_li">
					<div class="iq_authorization_li_head">
						Допустимая длина поля «'.$data['name'].'»
					</div>
					<div class="iq_authorization_flexbox_st iq_authorization_flexbox_yc iq_authorization_flex_gap_2">
						<div>
							<input type="text" id="'.$key.'_length_min" name="'.$key.'_length_min" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="10" placeholder="" value="'.$FormVal[$key.'_length_min'].'" onkeyup="return IQAuthOnlyNum(this.id);">
						</div>
						<div>
							-
						</div>
						<div>
							<input type="text" id="'.$key.'_length_max" name="'.$key.'_length_max" class="iq_authorization_input iq_authorization_input_default iq_authorization_max_w70 iq_authorization_align_center" maxlength="10" placeholder="" value="'.$FormVal[$key.'_length_max'].'" onkeyup="return IQAuthOnlyNum(this.id);">
						</div>
					</div>
				</li>
			';
			$FormArr[$key.'_regex'] = '
				<li class="iq_authorization_li">
					<div class="iq_authorization_li_head">
						REGEX валидация для поля «'.$data['name'].'»
					</div>
					<input type="text" id="'.$key.'_regex" name="'.$key.'_regex" class="iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w700" maxlength="128" placeholder="" value="'.$FormVal[$key.'_regex'].'">
				</li>
			';
			$FormArr[$key.'_regex_text'] = '
				<li class="iq_authorization_li">
					<div class="iq_authorization_li_head">
						Сообщение для пользователя если валидация REGEX не пройдена
					</div>
					<textarea id="'.$key.'_regex_text" name="'.$key.'_regex_text" class="iq_authorization_textarea iq_authorization_full_width_b iq_authorization_max_w700" maxlength="300" rows="3">'.$FormVal[$key.'_regex_text'].'</textarea>
				</li>
			';
		}
	
		?>
		</div>
		
		<form method="POST" enctype="multipart/form-data">
			<input type="hidden" name="form_save" value="1">
			<div class="iq_authorization_flexbox_tab iq_authorization_flex_gap">
			
				<div class="iq_authorization_flex_400">
					<div class="iq_authorization_list_block iq_authorization_flex_400 iq_authorization_flexh">
						<ul class="iq_authorization_ulcl">
							<?=$FormArr['auth_redirect'];?>
							<?=$FormArr['captcha_pub'];?>
							<?=$FormArr['captcha_secret'];?>
							<?=$FormArr['captcha_limit_free'];?>
						</ul>
					</div>
					
					<div class="iq_authorization_list_block iq_authorization_flex_400 iq_authorization_flexh">
						<h2 class="iq_authorization_flextbl">
							Логотип
						</h2>
						<ul class="iq_authorization_ulcl">
							<?=$FormArr['form_logo'];?>
						</ul>
					</div>
				</div>
			
				<div class="iq_authorization_flex_400">
					<div class="iq_authorization_list_block iq_authorization_flex_400 iq_authorization_flexh">
						<h2 class="iq_authorization_flextbl">
							Валидация логина
						</h2>
						<ul class="iq_authorization_ulcl">
							<?php
							$key = 'login';
							echo $FormArr[$key.'_length'];
							echo $FormArr[$key.'_regex'];
							echo $FormArr[$key.'_regex_text'];
							?>
						</ul>
					</div>
				
					<div class="iq_authorization_list_block iq_authorization_flex_400 iq_authorization_flexh">
						<h2 class="iq_authorization_flextbl">
							Валидация Email
						</h2>
						<ul class="iq_authorization_ulcl">
							<?php
							$key = 'email';
							echo $FormArr[$key.'_length'];
							echo $FormArr[$key.'_regex'];
							echo $FormArr[$key.'_regex_text'];
							?>
						</ul>
					</div>
				
					<div class="iq_authorization_list_block iq_authorization_flex_400 iq_authorization_flexh">
						<h2 class="iq_authorization_flextbl">
							Валидация пароля
						</h2>
						<ul class="iq_authorization_ulcl">
							<?php
							$key = 'pass';
							echo $FormArr[$key.'_length'];
							echo $FormArr[$key.'_regex'];
							echo $FormArr[$key.'_regex_text'];
							?>
						</ul>
					</div>
				</div>
				
			</div>
			<button type="submit" class="iq_authorization_button_light_blue iq_authorization_button_margin">
				Сохранить
			</button>
		</form>
</div>