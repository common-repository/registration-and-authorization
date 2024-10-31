<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}

class IQAuthClass {
	/**
	 * Create plugin database
	 * @since 1.0.0
	 * @return boolean
	 */
	function createDB() {
		global $wpdb;
        $sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}iq_auth_settings` (
					`key` varchar (32) NOT NULL DEFAULT '',
					`params_json` text(0) NOT NULL DEFAULT '',
				PRIMARY KEY `key` (`key`)
			);
        ";
		$index = $wpdb->query($wpdb->prepare($sql));
		if($index) {
			// settings
            $sql = "
				SELECT
					*
				FROM
					`{$wpdb->prefix}iq_auth_settings`
				WHERE
					`key` = 'settings';
			";
			$result = $wpdb->get_row($wpdb->prepare($sql));
			if(!$result) {
                $params_def_arr = [];
                $sql = "
					INSERT INTO `{$wpdb->prefix}iq_auth_settings`
						(`key`,
						`params_json`)
					VALUES
						('settings',
						%s);
				";
                $wpdb->query($wpdb->prepare($sql, json_encode($params_def_arr)));
				
                $sql = "
					INSERT INTO `{$wpdb->prefix}iq_auth_settings`
						(`key`,
						`params_json`)
					VALUES
						('custom_fields',
						%s);
				";
                $wpdb->query($wpdb->prepare($sql, json_encode($params_def_arr)));
			}
		}
		
        $sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}iq_auth_code` (
					`service` varchar (32) NOT NULL DEFAULT '',
					`email` varchar (100) NOT NULL DEFAULT '',
					`code` varchar (32) NOT NULL DEFAULT '',
					`date` int(12) NOT NULL DEFAULT 0
			);
        ";
		$index = $wpdb->query($wpdb->prepare($sql));
		
        $sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}iq_auth_antiflood` (
					`service` varchar (32) NOT NULL DEFAULT '',
					`ip` varchar (255) NOT NULL DEFAULT '',
					`date` int(12) NOT NULL DEFAULT 0
			);
        ";
		$index = $wpdb->query($wpdb->prepare($sql));
		
        $sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}iq_auth_custom_fields` (
					`id` int(20) NOT NULL AUTO_INCREMENT,
					`type_id` int(5) NOT NULL DEFAULT 0,
					`name` varchar (100) NOT NULL DEFAULT '',
					`ph` varchar (100) NOT NULL DEFAULT '',
					`icon` varchar (100) NOT NULL DEFAULT '',
					`important` tinyint (2) NOT NULL DEFAULT 0,
					`enable` tinyint (2) NOT NULL DEFAULT 1,
					`params_json` text (0) NOT NULL DEFAULT '[]',
					`created` int (12) NOT NULL DEFAULT 0,
					`redate` int (12) NOT NULL DEFAULT 0,
					PRIMARY KEY `id` (`id`)
			);
        ";
		$index = $wpdb->query($wpdb->prepare($sql));
		return true;
	}

	function getRealIpAddr() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$szIP = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$szIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$szIP = $_SERVER['REMOTE_ADDR'];
		}
		return $szIP;
	}
	
	/**
	 * Add code in database
	 * @since 1.0.0
	 * @return boolean
	 */
	function antifloodAdd($service) {
		if(empty($service)) {
			return false;
		}
		
		global $wpdb;
		$iCurrentTime = time();
		$ip = $this->getRealIpAddr();
		
		$sql = "
			INSERT INTO `{$wpdb->prefix}iq_auth_antiflood`
				(`service`,
				`ip`,
				`date`)
			VALUES
				(%s,
				%s,
				%d);
		";
		
		$index = $wpdb->query($wpdb->prepare($sql,
											$service,
											$ip,
											$iCurrentTime));
		if($index) {
			return true;
		}
		return false;
	}
	
	/**
	 * Delete old rows from the database
	 * @since 1.0.0
	 * @return boolean
	 */
	function antifloodRemoveOld($service) {
		global $wpdb;
		$iCurrentTime = time();
		$iMaxTime = $iCurrentTime - (60*15);
		$sql = "
			DELETE FROM
				`{$wpdb->prefix}iq_auth_antiflood`
			WHERE
				`date` < %d
		";
		$index = $wpdb->query($wpdb->prepare($sql,
											$iMaxTime));
		return true;
	}
	
	/**
	 * Checking if the code has already been sent recently
	 * @since 1.0.0
	 * @return string
	 */
	function antifloodGetCount($service) {
		if(empty($service)) {
			return false;
		}
		$this->antifloodRemoveOld($service);
		$ip = $this->getRealIpAddr();
		
		global $wpdb;
		$iCurrentTime = time();
		$iMaxTime = $iCurrentTime - (60*15);
		$sql = "
			SELECT	
				COUNT(*) AS `count`
			FROM
				`{$wpdb->prefix}iq_auth_antiflood`
			WHERE
				`date` >= %d
			AND
				`service` = %s
			AND
				`ip` = %s;
		";
		$result = $wpdb->get_row($wpdb->prepare($sql,
											$iMaxTime,
											$service,
											$ip), ARRAY_A);
		if($result) {
			return (int)$result['count'];
		}
		return 0;
	}
	
	/**
	 * Removes rows from the database
	 * @since 1.0.0
	 * @return boolean
	 */
	function antifloodRemove($service) {
		if(empty($service)) {
			return false;
		}
		global $wpdb;
		$ip = $this->getRealIpAddr();
		
		$sql = "
			DELETE FROM
				`{$wpdb->prefix}iq_auth_antiflood`
			WHERE
				`service` = %s
			AND
				`ip` = %s
		";
		$index = $wpdb->query($wpdb->prepare($sql,
											$service,
											$ip));
		return true;
	}
	
	/**
	 * Add code in database
	 * @since 1.0.0
	 * @return boolean
	 */
	function codeSend($service, $email, $code) {
		if(empty($service) || empty($email) || empty($code)) {
			return false;
		}
		
		global $wpdb;
		$iCurrentTime = time();
		
		$this->codeRemoveOld();
		$sql = "
			INSERT INTO `{$wpdb->prefix}iq_auth_code`
				(`service`,
				`email`,
				`code`,
				`date`)
			VALUES
				(%s,
				%s,
				%s,
				%d);
		";
		$index = $wpdb->query($wpdb->prepare($sql,
											$service,
											$email,
											$code,
											$iCurrentTime));
		if($index) {
			return true;
		}
		return false;
	}
	
	
	/**
	 * Delete old codes from the database
	 * @since 1.0.0
	 * @return boolean
	 */
	function codeRemoveOld() {
		global $wpdb;
		$iCurrentTime = time();
		$iMaxTime = $iCurrentTime - (60*60);
		$sql = "
			DELETE FROM
				`{$wpdb->prefix}iq_auth_code`
			WHERE
				`date` < %d
		";
		$index = $wpdb->query($wpdb->prepare($sql,
											$iMaxTime));
		return true;
	}
	
	
	/**
	 * Checking if the code has already been sent recently
	 * @since 1.0.0
	 * @return string
	 */
	function codeGetAlready($service, $email) {
		if(empty($service) || empty($email)) {
			return false;
		}
		$this->codeRemoveOld();
		
		global $wpdb;
		$iCurrentTime = time();
		$iMaxTime = $iCurrentTime - (60*60);
		$sql = "
			SELECT	
				`code`
			FROM
				`{$wpdb->prefix}iq_auth_code`
			WHERE
				`date` >= %d
			AND
				`service` = %s
			AND
				`email` = %s
			ORDER BY
				`date` DESC
			LIMIT 1;
		";
		$result = $wpdb->get_row($wpdb->prepare($sql,
											$iMaxTime,
											$service,
											$email), ARRAY_A);
		if($result) {
			return $result['code'];
		}
		return '';
	}
	
	/**
	 * Removes code from the database
	 * @since 1.0.0
	 * @return boolean
	 */
	function codeRemove($service, $email) {
		if(empty($service) || empty($email)) {
			return false;
		}
		global $wpdb;
		$iCurrentTime = time();
		$iMaxTime = $iCurrentTime - (60*60);
		$sql = "
			DELETE FROM
				`{$wpdb->prefix}iq_auth_code`
			WHERE
				`service` = %s
			AND
				`email` = %s
		";
		$index = $wpdb->query($wpdb->prepare($sql,
											$service,
											$email));
		return true;
	}
}