function IQAuthCustomFieldAdd(pop, itemid) {
	pop = pop === undefined ? 1 : pop;
	itemid = itemid === undefined ? 0 : itemid;
	let pretag = '';
	if(pop) {
		pretag = 'pop_';
	}
	
	IQAuthLoader(1);
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/custom_field/data_custom_field_add.php",
		data:{'ItemID':itemid,
		'Pop':pop},
		success: function(data) {
			IQAuthLoader(0);
			
			let phtml = jQuery.parseHTML( data );
			let result = jQuery(phtml).filter("#pop").length;
			if(result) {
				if(!document.getElementById('modal')) {
					jQuery(document.body).append('<div id="modal"></div>');
				}
				
				jQuery('#modal').html(data);
				setTimeout(function(){
					jQuery('#pop_modal').addClass('pop_show');
					IQAuthCustomFieldTypeChange(pop);
					PopOutClose();
				}, 100);
				return true;
			}
			IQAuthNoticeStrErr('notice_block', 'err', data);
		}
	});
}

function IQAuthCustomFieldTypeChange(pop) {
	pop = pop === undefined ? 1 : pop;
	let pretag = '';
	if(pop) {
		pretag = 'pop_';
	}
	
	const block_type = pretag+'cf_type';
	const block_notice = pretag+'cf_notice_block';
	const block_out = pretag+'block_cf_type_change';
	if(!document.getElementById(block_type) ||
	!document.getElementById(block_out)) {
		return false;
	}
	const val = jQuery('#'+block_type).val();
	
	if(!document.getElementById(pretag+'json_data') ||
	!document.getElementById(pretag+'sign_data')) {
		return;
	}
	const json_data = jQuery('#'+pretag+'json_data').val();
	const sign_data = jQuery('#'+pretag+'sign_data').val();
				
	IQAuthLoader(1);
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/custom_field/data_custom_field_type_change.php",
		data:{'Val':val,
		'Pop':pop,
		'JsonData':json_data,
		'SignData':sign_data},
		success: function(data) {
			IQAuthLoader(0);
			if(!document.getElementById(block_type) ||
			!document.getElementById(block_out)) {
				return false;
			}
			jQuery('#'+block_out).hide();
			jQuery('#'+block_out).html(data);
			jQuery('#'+block_out).slideToggle(200);
		}
	});
}

function IQAuthCustomFieldAddDo(pop, item) {
	pop = pop === undefined ? 0 : pop;
	item = item === undefined ? 0 : item;
	let pretag = '';
	if(pop) {
		pretag = 'pop_';
	}
	
	const block_form = pretag+'form_data';
	const block_options = pretag+'block_options';
	const block_notice = pretag+'cf_notice_block';
	if(!document.getElementById(block_form)) {
		return;
	}
	
	if(!document.getElementById(pretag+'json_data') ||
	!document.getElementById(pretag+'sign_data')) {
		return;
	}
	const json_data = jQuery('#'+pretag+'json_data').val();
	const sign_data = jQuery('#'+pretag+'sign_data').val();
	
	let data = new FormData();
	data.append('JsonData', json_data);
	data.append('SignData', sign_data);
	
	let FormValues = [];
	let OptionValues = [];
	let value;
	jQuery('#'+block_form).find('select, input, textarea').each(function(){
		FormValues.push(this.id);
		if(jQuery(this).attr('type') == 'checkbox') {
			value = jQuery(this).is(':checked');
		} else {
			value = jQuery(this).val();
		}
		data.append( this.id, value );
	});
	data.append('Values', FormValues);
	data.append('Pop', pop);
	
	jQuery('#'+block_options).find('.option_class').each(function(){
		OptionValues.push(jQuery(this).val());
	});
	data.append('Options', OptionValues);
	
	IQAuthLoader(true);
	let ajax = new XMLHttpRequest();
	ajax.onload = ajax.onerror = function() {
		IQAuthLoader(false);
		if(!document.getElementById(block_form)) {
			return;
		}
		
		const phtml = jQuery.parseHTML( this.responseText );
		if(this.status == 200) {
			let result = jQuery(phtml).filter("#success").length;
			if(result) {
				// success
				if(item) {
					IQAuthCustomFieldItemReload(item);
				} else {
					location.reload();
				}
				PopClose();
				return true;
			}
		}
		IQAuthNoticeStrErr(block_notice, 'err', this.responseText);
	}
	ajax.open("POST", "/wp-content/plugins/iq_authorize/data/custom_field/data_custom_field_add_do.php", true);
	ajax.send(data);
}

function IQAuthCustomFieldAddOption(pop) {
	pop = pop === undefined ? 0 : pop;
	let pretag = '';
	if(pop) {
		pretag = 'pop_';
	}
	const block = pretag+'block_options';
	if(!document.getElementById(block)) {
		return;
	}
	const form_html = '<div class="iq_authorization_mt_5"><input type="text" name="'+pretag+'cf_option" class="option_class iq_authorization_input iq_authorization_input_default iq_authorization_full_width_b iq_authorization_max_w300" maxlength="128" placeholder="New option" value=""></div>';
	jQuery('#'+block).append(form_html);
}

function IQAuthCustomFieldDelete(item) {
	if(item <= 0) {
		return false;
	}
	
	if(!confirm('Are you sure you want to delete')) {
		return false;
	}
	
	Loader(true);
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/custom_field/data_custom_field_delete.php",
		data:{'ItemID':item},
		success: function(data) {
			Loader(false);
			
			let phtml = jQuery.parseHTML( data );
			let result = jQuery(phtml).filter("#success").length;
			if(result) {
				if(document.getElementById('cf_block_'+item)) {
					jQuery('#cf_block_'+item).fadeOut(200);
					setTimeout(function(){
						jQuery('#cf_block_'+item).remove();
					}, 200);
				}
				return true;
			}
			IQAuthNoticeStrErr('notice_block', 'err', data);
		}
	});
}
				
function IQAuthCustomFieldItemReload(item) {
	if(item <= 0) {
		return false;
	}
	
	const block = 'cf_block_'+item;
	if(!document.getElementById(block)) {
		return false;
	}
	jQuery('#'+block).css('opacity', 0.5);
	
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/custom_field/data_custom_field_item_reload.php",
		data:{'ItemID':item},
		success: function(data) {
			if(!document.getElementById(block)) {
				return false;
			}
			
			if(data) {
				const el_temp = document.getElementById(block);
				el_temp.insertAdjacentHTML('afterEnd', data);
				el_temp.remove();
			}
		}
	});
}