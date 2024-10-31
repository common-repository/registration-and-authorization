<?php
/**
 * Plugin Name: IQ Authorization
 * Description: Advanced form of authorization and registration of your users
 
 * Plugin URI:  https://lumpx.com/wp-plugins/iq-authorization
 * Author URI:  https://lumpx.com
 * Author:      LumpX
 * Network: false
 * Version:     1.0.1
 *
 * Text Domain: iq_authorization
 * Domain Path: /lang
 * Requires at least: 5.2
 * Requires PHP: 5.4
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Network:    false

 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
**/

// Restrict
if(!defined('ABSPATH')) {
	die();
}

define('iq_authorization_CORE_VERSION', '1.0.0');
define('iq_authorization_CORE_URL', plugin_dir_url( __FILE__ ) );
define('iq_authorization_CORE_DIR', plugin_dir_path( __FILE__ ) );
define('iq_authorization_PLUGIN_TAG', 'iqauth' ); // Do not change
define('iq_authorization_MENU_TAG', 'iq-auth-' ); // Do not change
define('iq_authorization_SECRET_CODE', 'oDkaErYFaueWIFLdP3pUg69liprq3iRKqPqzSzYTt0lK6jh12YZ5JBsp1LFTd58n' );
define('iq_authorization_TPL_DIR', iq_authorization_CORE_DIR.'/templates' );

define('iq_authorization_AUTH_PAGE', 'iq-login' );
define('iq_authorization_REGIST_PAGE', 'iq-register' );
define('iq_authorization_CACHE_SETTINGS_FILE', 'iq-settings' );
define('iq_authorization_CORE_IMAGE_DIR', iq_authorization_CORE_DIR.'/assets/img' );
define('iq_authorization_CORE_IMAGE_URL', iq_authorization_CORE_URL.'/assets/img' );

if ( ! class_exists( 'iq_authorization_CoreClass' ) ) {
	class iq_authorization_CoreClass {
		function initialize() {
			add_action('admin_enqueue_scripts', array($this, 'iq_authorization_enqueue_admin'));
			add_action('wp_enqueue_scripts', array($this, 'iq_authorization_enqueue_front'));
			add_action('admin_menu', 'iq_authorization_pre_create_menu');
			add_action( 'plugins_loaded', array($this, 'iq_authorization_load_plugin_textdomain' ));
			add_action( 'init', array($this, 'iq_init' ));
			
			add_action( 'init',              array($this, 'iq_authorization_pages_redirect') );
			add_action( 'template_redirect', array($this, 'iq_authorization_pages_redirect') );
			add_filter( 'request',           array($this, 'iq_authorization_pages_redirect') );
			add_filter('pre_get_document_title', array($this, 'iq_authorization_change_title'));
			require_once iq_authorization_CORE_DIR.'/includes/inc_func.php';
		}


		function iq_init() {
			ob_start();
		}

		function wpse_alter_title( $vars = '' ) {
		}

		function iq_authorization_change_title( $title ) {
			$iq_auth_settings_arr = func_iq_authorization_get_settings();
			if(isset($iq_auth_settings_arr['login_url'])) {
				$login_page = $iq_auth_settings_arr['login_url'];
			} else {
				$login_page = iq_authorization_AUTH_PAGE;
			}
			if(isset($iq_auth_settings_arr['regist_url'])) {
				$regist_page = $iq_auth_settings_arr['regist_url'];
			} else {
				$regist_page = iq_authorization_REGIST_PAGE;
			}
			
			if(get_query_var( $login_page )) {
				if(isset($iq_auth_settings_arr['login_title'])) {
					$title = $iq_auth_settings_arr['login_title'];
				}
			}
			else if(get_query_var( $regist_page )) {
				if(isset($iq_auth_settings_arr['regist_title'])) {
					$title = $iq_auth_settings_arr['regist_title'];
				}
			}
			return $title;
		}
		
		function iq_authorization_pages_redirect( $vars = '' ) {
			$iq_auth_settings_arr = func_iq_authorization_get_settings();
			if(isset($iq_auth_settings_arr['login_url'])) {
				$login_page = $iq_auth_settings_arr['login_url'];
			} else {
				$login_page = iq_authorization_AUTH_PAGE;
			}
			if(isset($iq_auth_settings_arr['regist_url'])) {
				$regist_page = $iq_auth_settings_arr['regist_url'];
			} else {
				$regist_page = iq_authorization_REGIST_PAGE;
			}
			
			$hook = current_filter();
			if($hook == 'template_redirect') {
				if(get_query_var( $login_page )) {
					include iq_authorization_CORE_DIR.'/data/data_auth_index.php';
					die();
				}
				if(get_query_var( $regist_page )) {
					include iq_authorization_CORE_DIR.'/data/data_regist_index.php';
					die();
				}
			}
			if($hook == 'init') {
				global $wp_rewrite; 
				
				add_rewrite_endpoint( $login_page, EP_ROOT );
				add_rewrite_endpoint( $regist_page, EP_ROOT );
				
				$wp_rewrite->flush_rules();
			}
			if($hook == 'request') {
				
				if(isset ( $vars[$login_page] )
				&& empty ( $vars[$login_page] )
				&& $vars[$login_page] = 'default') {
					
				}
				
				if(isset ( $vars[$regist_page] )
				&& empty ( $vars[$regist_page] )
				&& $vars[$regist_page] = 'default') {
					
				}
			}

			return $vars;
		}

		static function iq_authorization_activation() {
			if(!isset($cIQAuthClass)) {
				require_once iq_authorization_CORE_DIR.'/includes/inc_auth.php';
				$cIQAuthClass = new IQAuthClass();
			}
			$cIQAuthClass->createDB();
			
			flush_rewrite_rules();
		}

		public static function iq_authorization_deactivation() {
			flush_rewrite_rules();
		}

		public static function iq_authorization_uninstall() {
		}

		function iq_authorization_enqueue_front() {
			wp_enqueue_style('iq_authorization_FrontViewStyle', iq_authorization_CORE_URL.'/assets/front/css/styles.css', false, '1.0');
			wp_enqueue_script('iq_authorization_FrontViewScript', iq_authorization_CORE_URL.'/assets/front/js/scripts.js', false, '1.0');
			wp_enqueue_style('iq_authorization_IconsViewScript', iq_authorization_CORE_URL. '/assets/icofont/icofont.min.css', false, '1.0');
			
			wp_enqueue_style('iq_authorization_FrontPopStyle', iq_authorization_CORE_URL.'/assets/front/css/pop.css', false, '1.0');
			wp_enqueue_script('iq_authorization_FrontPopScript', iq_authorization_CORE_URL.'/assets/front/js/pop.js', false, '1.0');
		}

		function iq_authorization_enqueue_admin() {
			wp_enqueue_style('iq_authorization_AdminViewStyle', iq_authorization_CORE_URL.'/assets/admin/css/styles.css', false, '1.1');
			wp_enqueue_style('iq_authorization_AdminLoaderStyle', iq_authorization_CORE_URL.'/assets/admin/css/loader.css', false, '1.0');
			wp_enqueue_script('iq_authorization_AdminViewScript', iq_authorization_CORE_URL.'/assets/admin/js/scripts.js', false, '1.0');
			wp_enqueue_style('iq_authorization_IconsViewScript', iq_authorization_CORE_URL. '/assets/icofont/icofont.min.css', false, '1.0');
			
			wp_enqueue_style('iq_authorization_AdminPopStyle', iq_authorization_CORE_URL.'/assets/admin/css/pop.css', false, '1.0');
			wp_enqueue_script('iq_authorization_AdminPopScript', iq_authorization_CORE_URL.'/assets/admin/js/pop.js', false, '1.0');
			wp_enqueue_script('iq_authorization_AdminCustomFieldsScript', iq_authorization_CORE_URL.'/assets/admin/js/custom_fields.js', false, '1.0');

		}
		function iq_authorization_load_plugin_textdomain() {
			load_plugin_textdomain( 'iq_authorization', false, dirname( plugin_basename( __FILE__ ) ). '/lang/' );
		}
	}
}

register_activation_hook( __FILE__, array( 'iq_authorization_CoreClass', 'iq_authorization_activation' ) );
register_deactivation_hook( __FILE__, array( 'iq_authorization_CoreClass', 'iq_authorization_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'iq_authorization_CoreClass', 'iq_authorization_uninstall' ) );

$iq_authorization_core_class = new iq_authorization_CoreClass();
$iq_authorization_core_class->initialize();