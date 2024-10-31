function IQAuthOnlyNum(id) {
	var val = jQuery('#'+id).val();
	var val_new = val.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
	jQuery('#'+id).val(val_new);
}
function IQAuthPreviewImage(id, input, next) {
	next = next === undefined ? false : next;
	var ext = input.files[0]['name'].substring(input.files[0]['name'].lastIndexOf('.') + 1).toLowerCase();
	if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == "webp" || ext == "svg")) {
		var reader = new FileReader();
		reader.onload = function (e) {
			jQuery('#'+id).attr('src', e.target.result);
			if(next) {
				jQuery('#'+next).attr('src', e.target.result);
			}
		}
		reader.readAsDataURL(input.files[0]);
	}
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