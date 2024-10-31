<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}
require_once iq_authorization_CORE_DIR.'/includes/inc_menu.php';

function func_iq_authorization_get_count_notification() {
	return 0;
}

function func_iq_authorization_get_settings() {
	if(iq_authorization_CACHE_SETTINGS_FILE) {
		$FILE = iq_authorization_CORE_DIR.'/cache/.'.iq_authorization_CACHE_SETTINGS_FILE;
		if(file_exists($FILE)) {
			$json_p = file_get_contents($FILE);
			if($json_p) {
				$result = json_decode(func_iq_authorization_out_protect($json_p), true);
				if($result) {
					return $result;
				}
			}
		}
	}
	
    global $wpdb;
    $sql = "
		SELECT
			`params_json`
		FROM
			`{$wpdb->prefix}iq_auth_settings`
		WHERE
			`key` = 'settings';
	";
    $result = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
    if($result) {
        $result = (array)$result;
        $result = json_decode($result['params_json'], true);
    }
		
	if(iq_authorization_CACHE_SETTINGS_FILE) {
		$FILE = iq_authorization_CORE_DIR.'/cache/.'.iq_authorization_CACHE_SETTINGS_FILE;
		file_put_contents($FILE, func_iq_authorization_in_protect(json_encode($result)));
	}
    return $result;
}

/**
 * Update settings plugin database
 * @since 1.0.0
 * @return boolean
 */
function func_iq_authorization_updateSettings($arr) {
    global $wpdb;
    $sql = "
		UPDATE
			`{$wpdb->prefix}iq_auth_settings`
		SET
			`params_json` = %s
		WHERE
			`key` = 'settings'
	";
    $wpdb->query($wpdb->prepare($sql, json_encode($arr)));
	
	if(iq_authorization_CACHE_SETTINGS_FILE) {
		$FILE = iq_authorization_CORE_DIR.'/cache/.'.iq_authorization_CACHE_SETTINGS_FILE;
		file_put_contents($FILE, func_iq_authorization_in_protect(json_encode($arr)));
	}
    return true;
}

function func_iq_authorization_in_protect($str, $secret=iq_authorization_SECRET_CODE, $IsSQL = false) {
	if($IsSQL) {
		if(empty($str)) {
			return '\'\'';
		}
		$szCrypt = "HEX(AES_ENCRYPT( '".$str."', SUBSTR(SHA2('".$secret."', 512), 1, 16)))";
	} else {
		if(empty($str)) {
			return false;
		}
		$ciphering = "AES-128-ECB";
		$szCrypt = strtoupper(bin2hex(base64_decode(
			openssl_encrypt(
				$str, 
				$ciphering, 
				substr(openssl_digest( $secret, 'sha512'), 0, 16)
			)
		)));
	}
	return $szCrypt;
}

function func_iq_authorization_out_protect($str, $secret = iq_authorization_SECRET_CODE) {
	$ciphering = "AES-128-ECB";
	$decryption = openssl_decrypt(
					base64_encode(hex2bin($str)), 
					$ciphering,
					substr(openssl_digest( $secret, 'sha512'), 0, 16)
				);
			
	return $decryption;
}

function func_iq_authorization_get_sign_json_array_protect($json_arr, $secret = iq_referral_system_SECRET_CODE) {
	$params1 = [
		'json' => $json_arr,
		'sole' => 'jeo4ZQBOeOxmOqXwk7nDK8RF8OscdJhj',
		'code' => $secret,
	];	
	$hash1 = hash('sha256', join('{sign1_protect}', $params1));
	$hash2 = md5($json_arr . $secret . $hash1);
	$params3 = [
		'json' => $json_arr,
		'sole' => 'DQIcnrHTRGqdzdw1QI0VcnAp7xa91Qlt',
		'sole2' => 'RNyyoRBbKPx6ThDbYyQJyG1r7THCq52Q',
	];	
	$hash3 = hash('sha256', join('{sign2_protect}', $params3));
	$hash4 = md5($json_arr . $secret . $hash3);
	$hash = $hash1.'-'.$hash2.'-'.$hash3.'-'.$hash4;
	return $hash;
}

function func_iq_authorization_get_count_letter($string) {
	$string = str_replace( "\r\n","\n", $string );
	return mb_strlen($string);
}

function func_iq_authorization_gen_num($length = 4) {
	$chars = '1234567890';
	$iNumChars = strlen($chars);
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $iNumChars) - 1, 1);
	}
	return $string;
}

function func_iq_authorization_gen_pass($length = 8, $symb = false) {
	$chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
	if($symb) {
		$chars .= '!@#$%^*()-+=';
	}
	$iNumChars = strlen($chars);
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $iNumChars) - 1, 1);
	}
	return $string;
}