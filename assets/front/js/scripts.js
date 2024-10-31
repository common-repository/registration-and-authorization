function IQAuthNoticeStrErr(block_out, type, msg) {
	if(!document.getElementById(block_out)) {
		return false;
	}
	if(!msg) {
		jQuery('#'+block_out).text('');
		return false;
	}
	
	let str = '';
	switch(type) {
		case 'err': {
			str = '<div class="iq_authorization_alert_block iq_authorization_err">'+msg+'</div>';
			break;
		}
		case 'success': {
			str = '<div class="iq_authorization_alert_block iq_authorization_ok">'+msg+'</div>';
			break;
		}
		case 'info': {
			str = '<div class="iq_authorization_alert_block iq_authorization_info">'+msg+'</div>';
			break;
		}
	}
	if(!str) {
		return false;
	}
	jQuery('#'+block_out).html(str);
}

function IQAuthLoader(v) {
    v = v === undefined ? 1 : v;
	const block = 'loader_block';
	if(!document.getElementById(block)) {
		const el = document.body;
		el.insertAdjacentHTML('afterEnd', '<div id="loader_block"></div>');
	}
	if(v) {
		jQuery('#'+block).html('<div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>');
		jQuery(document.body).css('opacity', 0.5);
		jQuery(document.body).addClass('disabled');
	} else {
		jQuery('#'+block).text('');
		jQuery(document.body).css('opacity', 1);
		jQuery(document.body).removeClass('disabled');
	}
}

function IQAuthLoginDo(pop) {
	pop = pop === undefined ? 0 : pop;
	let pretag = '';
	if(pop) {
		pretag = 'pop_';
	}
	const block_form = pretag+'form_iq_login';
	const block_out = pretag+'form_notice';
	const block_captcha = pretag+'form_captcha';
	if(!document.getElementById(block_form)) {
		return false;
	}
	
	let data = new FormData();
	let FormValues = [];
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
	
	IQAuthLoader(true);
	jQuery('#'+block_captcha).text('');
	jQuery('#'+block_out).text('');
	const ajax = new XMLHttpRequest();
	ajax.onload = ajax.onerror = function() {
		IQAuthLoader(false);
		if(!document.getElementById(block_form)) {
			return;
		}
		const phtml = jQuery.parseHTML( this.responseText );
		if(this.status == 200) {
			let result = jQuery(phtml).filter("#success").length;
			if(result) {
				IQAuthNoticeStrErr(block_out, 'success', this.responseText);
				
				if(document.getElementById('temp_redirect_url')) {
					const redirect_url = jQuery('#temp_redirect_url').val();
					window.location.href = redirect_url;
				}
				return true;
			}
			
			result = jQuery(phtml).filter("#auth_already").length;
			if(result) {
				IQAuthNoticeStrErr(block_out, 'success', this.responseText);
				
				if(document.getElementById('temp_redirect_url')) {
					const redirect_url = jQuery('#temp_redirect_url').val();
					window.location.href = redirect_url;
				}
				return true;
			}
			
			result = jQuery(phtml).filter("#captcha_block").length;
			if(result) {
				jQuery('#'+block_captcha).html(this.responseText);
				return true;
			}
		}
		IQAuthNoticeStrErr(block_out, 'err', this.responseText);
	}
	ajax.open("POST", "/wp-content/plugins/iq_authorize/data/data_login_do.php", true);
	ajax.send(data);
}

function IQAuthRegistDo(pop) {
	pop = pop === undefined ? 0 : pop;
	let pretag = '';
	if(pop) {
		pretag = 'pop_';
	}
	const block_form = pretag+'form_iq_regist';
	const block_out = pretag+'form_notice';
	const block_captcha = pretag+'form_captcha';
	if(!document.getElementById(block_form)) {
		return false;
	}
	
	let data = new FormData();
	let FormValues = [];
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
	
	IQAuthLoader(true);
	jQuery('#'+block_captcha).text('');
	const ajax = new XMLHttpRequest();
	ajax.onload = ajax.onerror = function() {
		IQAuthLoader(false);
		if(!document.getElementById(block_form)) {
			return;
		}
		const phtml = jQuery.parseHTML( this.responseText );
		if(this.status == 200) {
			let result = jQuery(phtml).filter("#success").length;
			if(result) {
				IQAuthNoticeStrErr(block_out, 'success', this.responseText);
				
				if(document.getElementById('temp_redirect_url')) {
					const redirect_url = jQuery('#temp_redirect_url').val();
					window.location.href = redirect_url;
				}
				return true;
			}
			
			result = jQuery(phtml).filter("#auth_already").length;
			if(result) {
				IQAuthNoticeStrErr(block_out, 'success', this.responseText);
				
				if(document.getElementById('temp_redirect_url')) {
					const redirect_url = jQuery('#temp_redirect_url').val();
					window.location.href = redirect_url;
				}
				return true;
			}
			
			result = jQuery(phtml).filter("#confirm_block").length;
			if(result) {
				jQuery('#'+block_out).html(this.responseText);
				return true;
			}
			
			result = jQuery(phtml).filter("#captcha_block").length;
			if(result) {
				jQuery('#'+block_captcha).html(this.responseText);
				return true;
			}
		}
		IQAuthNoticeStrErr(block_out, 'err', this.responseText);
	}
	ajax.open("POST", "/wp-content/plugins/iq_authorize/data/data_regist_do.php", true);
	ajax.send(data);
}

function RegistPage() {
	if(!document.getElementById('iq_auth_regist_page')) {
		return false;
	}
	const iq_auth_regist_page = jQuery('#iq_auth_regist_page').val();
	IQAuthUpdateURL('/'+iq_auth_regist_page);
	const block = 'content';
	if(!document.getElementById(block)) {
		location.reload();
		return false;
	}
	
	IQAuthLoader(1);
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/data_regist_index.php",
		data:{},
		success: function(data) {
			IQAuthLoader(0);
			if(!document.getElementById(block)) {
				return false;
			}
			jQuery('#'+block).html(data);
			IQAuthUpdateHeaders();
			IQAuthCustomFieldsLoad();
		}
	});
}

function LoginPage() {
	if(!document.getElementById('iq_auth_login_page')) {
		return false;
	}
	const iq_auth_login_page = jQuery('#iq_auth_login_page').val();
	IQAuthUpdateURL('/'+iq_auth_login_page);
	const block = 'content';
	if(!document.getElementById(block)) {
		location.reload();
		return false;
	}
	
	IQAuthLoader(1);
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/data_auth_index.php",
		data:{},
		success: function(data) {
			IQAuthLoader(0);
			if(!document.getElementById(block)) {
				return false;
			}
			jQuery('#'+block).html(data);
			IQAuthUpdateHeaders();
		}
	});
}

function IQAuthUpdateURL(full_url) {
	if(!full_url)	return;
    if (history.pushState) {
        history.pushState(null, null, full_url);
    }
}

function IQAuthUpdateHeaders() {
	const title_block = 'iq_auth_title';
	if(document.getElementById(title_block)) {
		const title_val = jQuery('#'+title_block).val();
		jQuery('#'+title_block).remove();
		document.title = title_val;
	}
}

function IQAuthCustomFieldsLoad() {
	const block = 'regist_custom_fields';
	if(!document.getElementById(block)) {
		return false;
	}
	
	jQuery.ajax({
		type: "POST",
		url: "/wp-content/plugins/iq_authorize/data/custom_field/data_custom_field_regist_load.php",
		data:{},
		success: function(data) {
			if(!document.getElementById(block)) {
				return false;
			}
			const el_temp = document.getElementById(block);
			el_temp.insertAdjacentHTML('afterEnd', data);
			el_temp.remove();
				
			jQuery('#'+block).hide();
			jQuery('#'+block).slideToggle(200);
		}
	});
}

function IQAuthOnlyNum(id) {
	var val = jQuery('#'+id).val();
	var val_new = val.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
	jQuery('#'+id).val(val_new);
}