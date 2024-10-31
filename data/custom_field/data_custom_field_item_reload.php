<?php
if(defined("ABSPATH")) {
	$AjaxRequest = false;
	if(!defined("iq_authorization_CORE_DIR")) { die(); }
} else {
	$AjaxRequest = true;
}

if($AjaxRequest) {
	if(!isset($_SERVER['HTTP_REFERER'])) { die(); }

	$iPostItemID = 0;
	if(isset($_POST['ItemID'])) {
		$iPostItemID = (int)$_POST['ItemID'];
	}

	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	require_once WP_PLUGIN_DIR.'/iq_authorize/includes/inc_func.php';

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

	$SQL_Search = "
		AND
			a.`id` = '".$iPostItemID."'
	";
	$users = $cIQAuthCustomFields->getCustomFields([], $SQL_Search);
	$q = [];
	if($users) {
		foreach($users AS $data) {
			$q = $data;
			break;
		}
	}
	if(!$q) {
		die();
	}
}
?>
<tr id="cf_block_<?=$q['id'];?>">
	<td data-label="Название поля">
		<?=$q['name'];?>
	</td>
	<td data-label="Тип поля">
		<?=esc_html__($q['type_name'], 'iq_authorize');?>
	</td>
	<td data-label="Иконка">
		<?php
		if(!empty($q['icon'])) {
			echo '<span class="'.$q['icon'].'"></span>';
		} else {
			echo '-';
		}
		?>
	</td>
	<td data-label="Обязательно">
		<?php
		if(!empty($q['important'])) {
			echo '<span class="iq_authorization_color_green">Да</span>';
		} else {
			echo '<span class="iq_authorization_color_gray">Нет</span>';
		}
		?>
	</td>
	<td data-label="Статус">
		<?php
		if(!empty($q['enable'])) {
			echo '<span class="iq_authorization_color_green">Вкл</span>';
		} else {
			echo '<span class="iq_authorization_color_red">Откл</span>';
		}
		?>
	</td>
	<td data-label="Создано">
		<?=date('d.m.Y H:i', $q['created']);?>
	</td>
	<td data-label="Действие">
		<a href="#" onclick="IQAuthCustomFieldAdd(1, <?=$q['id'];?>);return false;">
			<span class="dashicons dashicons-edit"></span>
		</a>
		<a href="#" onclick="IQAuthCustomFieldDelete(<?=$q['id'];?>);return false;">
			<span class="dashicons dashicons-trash"></span>
		</a>
	</td>
</tr>