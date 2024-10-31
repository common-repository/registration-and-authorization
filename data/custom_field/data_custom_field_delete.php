<?php
if(!isset($_SERVER['HTTP_REFERER'])) { die(); }

$iPostItemID = 0;
if(isset($_POST['ItemID'])) {
	$iPostItemID = (int)$_POST['ItemID'];
}

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

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

$sql = "
	DELETE FROM
		`{$wpdb->prefix}iq_auth_custom_fields`
	WHERE
		`id` = %d;
";
$bResult = $wpdb->query($wpdb->prepare($sql, $iPostItemID));
if(!$bResult) {
	$szMsg = esc_html__('Error', 'iq_authorize');
	die($szMsg);
}
?>
<div id="success"></div>