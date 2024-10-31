<?php
if(!defined('ABSPATH')) {
	die();
}
?>
<div class="iq_authorization_acenter">
	<input type="hidden" id="iq_auth_login_page" value="<?=$login_page;?>">
	<?php
		if(isset($iq_auth_settings_arr['regist_title'])) {
			$title = $iq_auth_settings_arr['regist_title'];
			?>
			<input type="hidden" id="iq_auth_title" value="<?=$title;?>">
			<?php
		}
	?>
	
	<div class="iq_authorization_login_block">
		<?=$FormArr['logo'];?>
		<div class="iq_authorization_login_head_txt">
			Регистрация
		</div>
		<form method="POST" onsubmit="IQAuthRegistDo(<?=$bPop;?>);return false;">
			<ul id="<?=$szPreTag;?>form_iq_regist" class="iq_authorization_ulcl">
				<?=$FormArr['login'];?>
				<?=$FormArr['email'];?>
				<?=$FormArr['pass'];?>
				<?=$FormArr['repass'];?>
				
				<?php
				if(!$AjaxRequest) {
					include iq_authorization_CORE_DIR . '/data/custom_field/data_custom_field_regist_load.php';
				} else {
					?>
					<li id="<?=$szPreTag;?>regist_custom_fields" class="iq_authorization_li">
					</li>
					<?php
				}
				?>
	
				<?=$FormArr['notice'];?>
				<?=$FormArr['form_captcha'];?>
				<?=$FormArr['buttons'];?>
			</ul>
		</form>
	</div>
</div>