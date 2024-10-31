<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}

if(!isset($cIQAuthCustomFields)) {
	require_once iq_authorization_CORE_DIR.'/includes/inc_custom_fields.php';
	$cIQAuthCustomFields = new IQAuthCustomFields();
}
$iq_custom_fields = $cIQAuthCustomFields->get();
?>
<div class="iq_authorization_core_block">
    <div class="iq_authorization_list_block iq_authorization_hided">
		<div class="iq_authorization_flexbox_st iq_authorization_flexbox_pos iq_authorization_flexbox_yc iq_authorization_flex_gap iq_authorization_mb15">
			<h2 class="iq_authorization_head_block">
				<?=esc_html__('Custom fields', 'iq_authorization_system');?>
			</h2>
			<div>
				<button class="iq_authorization_button_light_blue" onclick="IQAuthCustomFieldAdd(1, 0);return false;">
					Новое поле
				</button>
			</div>
		</div>
		
		<?php
		################
		##### POST #####
		################
		$szErr = '';
		$bErr = false;
		if(isset($_POST['form_save'])) {
			
			$bSave = $cIQAuthCustomFields->update($iq_custom_fields);
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
			В данном разделе добавляйте и настраивайте свои произвольные поля при регистрации
		</div>
		<div id="notice_block"></div>
		
		<?php
		$FormArr = [];
		$FormVal = [
			'regist_title' => '',
		];
		if(isset($iq_custom_fields['regist_title'])) {
			$FormVal['regist_title'] = $iq_custom_fields['regist_title'];
		}

		?>
		<div id="custom_fields_content"></div>
		<div id="list_data">
			<?php
			include dirname( __FILE__ ).'/iq_authorization_custom_fields_tbl.php';
			?>
		</div>
	</div>
</div>