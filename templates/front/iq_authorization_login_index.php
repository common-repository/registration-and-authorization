<?php
if(!defined('ABSPATH')) {
	die();
}
?>
<div class="iq_authorization_acenter">
	<input type="hidden" id="iq_auth_regist_page" value="<?=$regist_page;?>">
	<?php
		if(isset($iq_auth_settings_arr['login_title'])) {
			$title = $iq_auth_settings_arr['login_title'];
			?>
			<input type="hidden" id="iq_auth_title" value="<?=$title;?>">
			<?php
		}
	?>
	
	<div class="iq_authorization_login_block">
		<?=$FormArr['logo'];?>
		<div class="iq_authorization_login_head_txt">
			Вход в аккаунт
		</div>
		<form method="POST" onsubmit="IQAuthLoginDo(<?=$bPop;?>);return false;">
			<ul id="<?=$szPreTag;?>form_iq_login" class="iq_authorization_ulcl">
				<?=$FormArr['login'];?>
				<?=$FormArr['pass'];?>
				<?=$FormArr['notice'];?>
				<?=$FormArr['form_captcha'];?>
				<?=$FormArr['buttons'];?>
			</ul>
		</form>
	</div>
</div>