<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}

function iq_authorization_pre_create_menu() {
	$notification_count = func_iq_authorization_get_count_notification(-1);
	iq_authorization_create_menu($notification_count);
}
function iq_authorization_create_menu($notification_count) {
	add_menu_page(
		'IQ Authorization',
		$notification_count ? sprintf('%s <span class="awaiting-mod">%d</span>', 'IQ Authorization', $notification_count) : 'IQ Authorization',
		'manage_options',
		iq_authorization_MENU_TAG.'index',
		'iq_authorization_menu_core_callback',
		'dashicons-buddicons-buddypress-logo',
		50 );

	add_submenu_page(
		iq_authorization_MENU_TAG.'index',
		esc_html__('Authorization', 'iq_authorization'),
		esc_html__('Authorization', 'iq_authorization'),
		'manage_options',
		iq_authorization_MENU_TAG.'login-page',
		'iq_authorization_menu_auth_callback',
		1
	);

	add_submenu_page(
		iq_authorization_MENU_TAG.'index',
		esc_html__('Registration', 'iq_authorization'),
		esc_html__('Registration', 'iq_authorization'),
		'manage_options',
		iq_authorization_MENU_TAG.'regist-page',
		'iq_authorization_menu_regist_callback',
		2
	);

	add_submenu_page(
		iq_authorization_MENU_TAG.'index',
		esc_html__('Custom fields', 'iq_authorization'),
		esc_html__('Custom fields', 'iq_authorization'),
		'manage_options',
		iq_authorization_MENU_TAG.'custom-fields-page',
		'iq_authorization_menu_custom_fields_callback',
		3
	);

}
function iq_authorization_menu_core_callback() {
	include iq_authorization_CORE_DIR . '/templates/iq_authorization_index.php';
}
function iq_authorization_menu_auth_callback() {
	include iq_authorization_CORE_DIR . '/templates/iq_authorization_login_page.php';
}
function iq_authorization_menu_regist_callback() {
	include iq_authorization_CORE_DIR . '/templates/iq_authorization_regist_page.php';
}
function iq_authorization_menu_custom_fields_callback() {
	include iq_authorization_CORE_DIR . '/templates/iq_authorization_custom_fields_page.php';
}