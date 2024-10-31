<?php
// Restrict
if(!defined('ABSPATH') || !defined('iq_authorization_CORE_DIR')) {
	die();
}

define('iq_authorization_CACHE_CUSTOM_FIELDS_FILE', 'iq-custom-fields' );
define('iq_authorization_CACHE_CUSTOM_FIELDS_LINES', 'iq-custom-fields-lines' );
class IQAuthCustomFields {
	/**
	 * Get custom fields types
	 * @since 1.0.0
	 * @return array
	 */
	function getTypes() {
		global $wpdb;
		$sql = "
			SELECT
				*
			FROM
				`{$wpdb->prefix}iq_auth_custom_field_types`;
		";
		$result = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);
		return $result;
	}
	/**
	 * Get settings custom fields
	 * @since 1.0.0
	 * @return array
	 */
	function get() {
		if(iq_authorization_CACHE_CUSTOM_FIELDS_FILE) {
			$FILE = iq_authorization_CORE_DIR.'/cache/.'.iq_authorization_CACHE_CUSTOM_FIELDS_FILE;
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
				`key` = 'custom_fields';
		";
		$result = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
		if($result) {
			$result = (array)$result;
			$result = json_decode($result['params_json'], true);
		}
			
		if(iq_authorization_CACHE_CUSTOM_FIELDS_FILE) {
			$FILE = iq_authorization_CORE_DIR.'/cache/.'.iq_authorization_CACHE_CUSTOM_FIELDS_FILE;
			file_put_contents($FILE, func_iq_authorization_in_protect(json_encode($result)));
		}
		return $result;
	}
	
	/**
	 * Update settings custom fields
	 * @since 1.0.0
	 * @return array
	 */
	function update($arr) {
		global $wpdb;
		$sql = "
			UPDATE
				`{$wpdb->prefix}iq_auth_settings`
			SET
				`params_json` = %s
			WHERE
				`key` = 'custom_fields'
		";
		$wpdb->query($wpdb->prepare($sql, json_encode($arr)));
		
		if(iq_authorization_CACHE_CUSTOM_FIELDS_FILE) {
			$FILE = iq_authorization_CORE_DIR.'/cache/.'.iq_authorization_CACHE_CUSTOM_FIELDS_FILE;
			file_put_contents($FILE, func_iq_authorization_in_protect(json_encode($arr)));
		}
		return true;
	}
	
	/**
	 * Update settings custom fields
	 * @since 1.0.0
	 * @return array
	 */
	function add($arr, $item_id = 0) {
		if(!isset($arr['type_field'])) {
			return false;
		}
		
		$params_arr = [];
		switch($arr['type_field']) {
			case 'input:text': {
				$params_arr = [
					'length_min' => $arr['length_min'],
					'length_max' => $arr['length_max'],
					'regex' => $arr['regex'],
					'regex_txt' => $arr['regex_txt'],
				];
				break;
			}
			case 'select': {
				$params_arr = [
					'select_name' => $arr['select_name'],
				];
				$params_arr['options_arr'] = [];
				for($i = 0; $i < count($arr['select_options_arr']); $i++) {
					$option = trim($arr['select_options_arr'][$i]);
					$params_arr['options_arr'][] = $option;
				}
				break;
			}
		}
		$params_json = json_encode($params_arr);
		
		global $wpdb;
		
		$iCurrentTime = time();
		if($item_id) {
			// edit
			$sql = "
				UPDATE 
					`{$wpdb->prefix}iq_auth_custom_fields`
				SET
					`type_id` = %d,
					`name` = %s,
					`ph` = %s,
					`icon` = %s,
					`important` = %d,
					`enable` = %d,
					`params_json` = %s,
					`redate` = %d
				WHERE
					`id` = %d
			";
			/*
			echo '<pre>' . print_r($wpdb->prepare($sql, 
										$arr['type'],
										$arr['name'],
										$arr['icon'],
										$arr['important'],
										$params_json,
										$iCurrentTime,
										$item_id,
										), true) . '</pre>';
										*/
			$index = $wpdb->query($wpdb->prepare($sql, 
										$arr['type'],
										$arr['name'],
										$arr['ph'],
										$arr['icon'],
										$arr['important'],
										$arr['enable'],
										$params_json,
										$iCurrentTime,
										$item_id,
										)
								 );
		} else {
			// create
			$sql = "
				INSERT INTO `{$wpdb->prefix}iq_auth_custom_fields`
					(`type_id`,
					`name`,
					`ph`,
					`icon`,
					`important`,
					`important`,
					`params_json`,
					`created`)
				VALUES
					(%d,
					'%s',
					'%s',
					'%s',
					'%d',
					'%d',
					'%s',
					'%d');
			";
			$index = $wpdb->query($wpdb->prepare($sql, 
										$arr['type'],
										$arr['name'],
										$arr['ph'],
										$arr['icon'],
										$arr['important'],
										$arr['enable'],
										$params_json,
										$iCurrentTime,
										)
								 );
		}
		if($index) {
			return true;
		}
		return false;
	}
	
	function getCustomFields($args = [], $search = '') {
        $sql_search = '';
        $sql_limit = '';
		if($search) {
			$sql_search .= "
                {$search}
            ";
		}
	    if($args) {
            $sql_limit .= "
                LIMIT 
                    {$args['offset']}, {$args['number']}
            ";
        }
		global $wpdb;
		$sql = "
			SELECT
				a.*,
				b.`name` AS `type_name`,
				b.`field` AS `type_field`,
				b.`type` AS `type_type`
			FROM
				`{$wpdb->prefix}iq_auth_custom_fields` a
			INNER JOIN
				`{$wpdb->prefix}iq_auth_custom_field_types` b
			ON
				a.`type_id` = b.`id`
			WHERE
			    a.`id` > 0
			    {$sql_search}
			ORDER BY
				a.`created` DESC
			    {$sql_limit}
		";
        // echo '<pre>' . print_r($sql, true) . '</pre>';

		$results = $wpdb->get_results(
			$wpdb->prepare($sql),
			ARRAY_A
		);
		return $results;
	}
}