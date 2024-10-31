var $ = jQuery.noConflict();
const LoaderStr = '<div class="loader_block"><img src="/wp-content/themes/yakniga/assets/images/aload.gif" class="loader"></div>';

function SearchProducts(more) {
	more = more === undefined ? false : more;
	
	const block = 'search_out';
	var sp = 'start_page';
	var sw = 0;
	let st = '';
			
	if(!document.getElementById('search_text')) {
		return false;
	}
	
	if(more) {
		sw = +jQuery('#'+sp).val();
		jQuery("#"+block).append('<div id="next_load">'+LoaderStr+'</div>');
	} else {
		let ref = jQuery('#search_text').val();
		if(ref.length >= 3) {
			setAttr('search',encodeURIComponent(ref));
		} else {
			setAttr('search','');
		}
	}
	
	var regexsearch = /search=([^&]+)/i;
	if (!!regexsearch.exec(document.location.search)) {
		st = regexsearch.exec(document.location.search)[1];
		if(st.length >= 3) {
			jQuery('#search_text').val(decodeURIComponent(st));
		}
	}
	
	$.ajax({
		type: "POST",
		url: "/wp-content/themes/yakniga/inc/search_order_post.php",
		data:{'StartPage':sw,
		'search':st},
		success: function(data) {
			if(more) {
				if(document.getElementById('next_load')) {
					jQuery('#next_load').remove();
				}
				if(document.getElementById('showmore')) {
					jQuery('#showmore').remove();
				}
				jQuery("#"+block).append(data);
				var np = +jQuery('#np_'+sp).val();
				jQuery('#'+sp).val(sw+np);
			} else {
				jQuery('#'+block).hide();
				jQuery('#'+block).html(data);
				jQuery('#'+block).slideToggle(700);
			}
		}
	});
}

function setAttr(key, value) {
	var baseUrl = [location.protocol, '//', location.host, location.pathname].join(''),
		urlQueryString = document.location.search,
		newParam = key + '=' + value,
		params = '?' + newParam;
    if (urlQueryString) {
        var updateRegex = new RegExp('([\?&])' + key + '[^&]*');
        var removeRegex = new RegExp('([\?&])' + key + '=[^&;]+[&;]?');
        if( typeof value == 'undefined' || value == null || value == '' ) {
            params = urlQueryString.replace(removeRegex, "$1");
            params = params.replace( /[&;]$/, "" );
        } else if (urlQueryString.match(updateRegex) !== null) {
            params = urlQueryString.replace(updateRegex, "$1" + newParam);
        } else {
            params = urlQueryString + '&' + newParam;
        }
    } else {
		if( typeof value == 'undefined' || value == null || value == '' )
			return true;
	}
    params = params == '?' ? '' : params;
    window.history.replaceState({}, "", baseUrl + params);
	return true;
};

function set_cookie( name, value, exp_y, exp_m, exp_d, path, domain, secure ) {
	var cookie_string = name + "=" + escape ( value );
	if ( exp_y ) {
		var expires = new Date ( exp_y, exp_m, exp_d );
		// console.log('expires set_cookie: '+expires.toGMTString());
		cookie_string += "; expires=" + expires.toGMTString();
	}
	if ( path )
		cookie_string += "; path=" + escape ( path );

	if ( domain )
		cookie_string += "; domain=" + escape ( domain );

	if ( secure )
		cookie_string += "; secure";

	document.cookie = cookie_string;
}
function get_cookie ( cookie_name ) {
	var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)' );
	if ( results )
		return ( unescape ( results[2] ) );
	else
		return null;
}

function CartShow() {
	const cart_block = 'cart_block';
	if(!document.getElementById(cart_block)) {
		return false;
	}
	
	const cart_str = get_cookie('cart');
	let iCountProduct = 0;
	let iCountTotal = 0;
	let str;
	if(cart_str) {
		let parts = cart_str.split(';');
		let val, parts2;
		for(var i = 0; i < parts.length; i++){
			val = parts[i].trim();
			if(!val) {
				continue;
			}
			parts2 = val.split(':');
			
			iCountTotal = iCountTotal+parseInt(parts2[1]);
			iCountProduct++;
		}
	}
	if(iCountProduct) {
		if(cart_str) {
			jQuery('#'+cart_block).html('<div class="cart_block"><div class="cart_head">Ваша корзина</div>Всего товаров: '+iCountProduct+'<br>Общее количество: '+iCountTotal+'<div class="mt_7"><button class="cart_button" onclick="BookOrder();">Оформить заказ</button></div></div>');
		} else {
			jQuery('#'+cart_block).text('');
		}
	} else {
		jQuery('#'+cart_block).text('');
	}
}

function CartRemove(id) {
	const cart_str = get_cookie('cart');
	if(cart_str) {
		let bUpdate = false;
		let parts = cart_str.split(';');
		let val, parts2;
		let iProductID, iProductCounts;
		let str_re = '';
		for(var i = 0; i < parts.length; i++){
			val = parts[i].trim();
			if(!val) {
				continue;
			}
			parts2 = val.split(':');
			iProductID = parseInt(parts2[0]);
			iProductCounts = parseInt(parts2[1]);
			if(iProductID == id) {
				// remove item from cart
				bUpdate = true;
			} else {
				if(str_re) {
					str_re = str_re+';';
				}
				str_re = str_re+iProductID+':'+iProductCounts;
			}
		}
		
		if(bUpdate) {
			set_cookie('cart', str_re, 2030, 10, 10, '/');
		}
	}
	CartShow();
}

function CartAdd(id) {
	//console.log(id);
	const block_button = 'cart_add_button_'+id;
	if(document.getElementById(block_button)) {
		jQuery('#'+block_button).css('opacity', 0.7);
	}
	
	const iCounts = +jQuery('#cart_counts_'+id).val();
	//console.log('cart_counts_'+id);
	//console.log(iCounts);
	if(iCounts <= 0) {
		CartRemove(id);
		return false;
	}
	
	const cart_str = get_cookie('cart');
	let iCountProduct = 1;
	let iCountTotal = iCounts;
	let str;
	if(cart_str) {
		let bUpdate = false;
		let parts = cart_str.split(';');
		let val, parts2;
		let iProductID, iProductCounts;
		let str_re = '';
		for(var i = 0; i < parts.length; i++){
			val = parts[i].trim();
			if(!val) {
				continue;
			}
			parts2 = val.split(':');
			iProductID = parseInt(parts2[0]);
			iProductCounts = parseInt(parts2[1]);
			if(iProductID !== id) {
				if(str_re) {
					str_re = str_re+';';
				}
				str_re = str_re+iProductID+':'+iProductCounts;
			}
		}
		
		if(str_re) {
			str_re = str_re+';';
		}
		str = str_re+id+':'+iCounts;
	} else {
		str = id+':'+iCounts;
	}
	
	set_cookie('cart', str, 2030, 10, 10, '/');
	
	CartShow();
}

function BookOrder(id) {
	Loader(true);
	
	$.ajax({
		type: "POST",
		url: "/wp-content/themes/yakniga/inc/search_order_form_post.php",
		data:{},
		success: function(data) {
			Loader(false);
			
			const phtml = jQuery.parseHTML( data );
			let result = jQuery(phtml).filter("#pop").length;
			if(result) {
				jQuery('#modal').html(data);
				setTimeout(function(){
					jQuery('#pop_modal').addClass('pop_show');
					PopOutClose();
				}, 200);
				return;
			}
			OrderShowMessage('error', data, 5000);
		}
	});
}

function OrderShowMessage(type, in_msg, delay) {
	if(!in_msg) { return false; }
	
	let msg = '';
	switch(type) {
		case 'success': {
			msg = '<div class="success_text">'+in_msg+'</div>';
			break;
		}
		case 'error': {
			msg = '<div class="error_text">'+in_msg+'</div>';
			break;
		}
	}
	if(msg) {
		jQuery('#modal_notice').html(msg);
		setTimeout(function(){ jQuery('#modal_notice').text(''); }, delay);
	}
}

function PopClose(num) {
	num = num === undefined ? '' : num;
	jQuery('#modal'+num).text('');
	jQuery(document).prop('onclick', null)  // Removes 'onclick' property if found
              .off('click');
}
function PopOutClose(num) {
	num = num === undefined ? '' : num;
	jQuery('#pop_modal').bind("mousedown", function (e){
		//console.log('click pop_modal');
		
		var div = jQuery("#pop_window");
		if (!div.is(e.target)
			&& div.has(e.target).length === 0) {
			//console.log('PopClose');
			PopClose(num);
		}
	});
}
function PopShow(time) {
	time = time === undefined ? 100 : time;
	if(time) {
		setTimeout(function(){
			jQuery('#pop_modal').addClass('pop_show');
			PopOutClose();
		}, time);
	} else {
		jQuery('#pop_modal').addClass('pop_show');
		PopOutClose();
	}
}

function OnlyNum(id) {
	var val = jQuery('#'+id).val();
	var val_new = val.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
	jQuery('#'+id).val(val_new);
}

function EmptyCart() {
	if(!confirm('Вы уверены что хотите очистить Вашу корзину?')) {
		return false;
	}
	set_cookie('cart', '', 1990, 10, 10, '/');
	PopClose();
	if(document.getElementById('cart_block')) {
		jQuery('#cart_block').text('');
	}
	if(document.getElementById('search_out')) {
		jQuery('#search_out').find('.input_order_counts').each(function(){
			jQuery(this).val('');
		});
	}
}

function BookOrderDo() {
	const block_form = 'pop_form_data';
	if(!document.getElementById(block_form)) {
		return;
	}
	const cart_form_tables = 'cart_form_tables';
	if(!document.getElementById(cart_form_tables)) {
		return;
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
	
	let items_str = '';
	jQuery('#'+cart_form_tables).find('.cart_form_item_id').each(function(){
		FormValues.push(this.id);
		
		let let_item_id = +jQuery(this).val();
		let block_temp = 'cart_form_counts_'+let_item_id;
		let item_count = +jQuery('#'+block_temp).val();
		if(items_str) {
			items_str = items_str+';';
		}
		items_str = items_str+let_item_id+':'+item_count;
	});
	data.append('Items', items_str);
	
	jQuery('#'+block_form).css('opacity', 0.5);
	jQuery('#pop_but_order_create').css('disabled', true);
	jQuery('#pop_result').html('');
	Loader(true);
	
	const ajax = new XMLHttpRequest();
	ajax.onload = ajax.onerror = function() {
		if(!document.getElementById(block_form)) {
			return;
		}
		jQuery('#'+block_form).css('opacity', 1);
		jQuery('#pop_but_order_create').css('disabled', false);
	
		const phtml = $.parseHTML( this.responseText );
		if(this.status == 200) {
			Loader(false);
			
			let result = jQuery(phtml).filter("#success").length;
			if(result) {
				// OK
				jQuery('#modal_notice').html('<div class="success_text">'+this.responseText+'</div>');
				setTimeout(function(){ jQuery('#modal_notice').text(''); }, 5000);
				jQuery('#cart_block').text('');
				PopClose();
				return true;
			}
		}
		
		jQuery('#pop_result').show();
		jQuery('#pop_result').html(this.responseText);
	}
	ajax.open("POST", "/wp-content/themes/yakniga/inc/search_order_form_do.php", true);
	ajax.send(data);
}

function CartFormRemove(id) {
	if(!confirm('Удалить?')) {
		return false;
	}
	if(document.getElementById('cart_form_item_'+id)) {
		jQuery('#cart_form_item_'+id).fadeOut(500);
		setTimeout(function(){ jQuery('#cart_form_item_'+id).remove(); }, 700);
	}
	CartRemove(id);
}

function ItemOnCount(block_id, id) {
	const block = 'pre_cart_items';
	if(!document.getElementById(block)) {
		return false;
	}
	
	var arr = {};
	var ca = $('#'+block).val();
	var key = id.toString().trim();
	var exists = false;
	const curr = +jQuery('#'+block_id).val();
	var iCounts = 0;
	
	if(ca !== '') {
		arr = JSON.parse(ca);
		if(arr[key] !== undefined) {
			exists = true;
		}
		
	}
	if(exists) {
		delete arr[key];
	}
	
	arr[key] = curr;
	setPreCart(JSON.stringify(arr));
	
	jQuery.each(arr, function(index, value) {
		iCounts += parseInt(value);
	});
	
	const cart_block = 'cart_block';
	if(iCounts) {
		jQuery('#'+cart_block).html('<div class="cart_block">Всего выбрано '+iCounts+' шт<br>Чтобы добавить их нажмите<div class="mt_7"><button class="precart_button" onclick="CartAddPreTo();">Обновить корзину</button></div></div>');
	} else {
		jQuery('#'+cart_block).html('<div class="cart_block">Были внесен изменения<br>Чтобы применить их нажмите<div class="mt_7"><button class="precart_button" onclick="CartAddPreTo();">Обновить корзину</button></div></div>');
	}
}

function getPreCart() {
	const block = 'pre_cart_items';
	const precart = jQuery('#'+block).val();
	return precart;
}

function setPreCart(str) {
	const block = 'pre_cart_items';
	jQuery('#'+block).val(str);
	return true;
}

function CartAddPreTo() {
	const block = 'search_out';
	if(!document.getElementById(block)) {
		return false;
	}
	
	var precart_arr = {};
	var cart_arr = {};
	var ca = jQuery('#pre_cart_items').val();
	if(ca !== '') {
		precart_arr = JSON.parse(ca);
	}
	//console.log('precart_arr: ' + JSON.stringify(precart_arr));
	
	let cart_key;
	const cart_str = get_cookie('cart');
	// console.log(cart_str);
	
	if(cart_str) {
		let str_re = '';
		let parts = cart_str.split(';');
		let val, parts2, count;
		for(var i = 0; i < parts.length; i++){
			val = parts[i].trim();
			if(!val) {
				continue;
			}
			parts2 = val.split(':');
			cart_key = parts2[0].toString().trim();
			count = parseInt(parts2[1]);
			cart_arr[cart_key] = count;
		}
	}
	//console.log('cart_arr: ' + JSON.stringify(cart_arr));
	
	// update cart
	if(precart_arr) {
		jQuery.each(precart_arr, function(index, value) {
			cart_key = index.toString().trim();
			if(value > 0) {
				cart_arr[cart_key] = value;
			} else {
				delete cart_arr[cart_key];
			}
		});
	}
	
	let str = '';
	jQuery.each(cart_arr, function(index, value) {
		cart_key = parseInt(index);
		if(str) {
			str = str+';';
		}
		str = str+cart_key+':'+value;
	});
	set_cookie('cart', str, 2030, 10, 10, '/');
	jQuery('#pre_cart_items').val('');
	CartShow();
}

function in_array(value, array) {
    for(let i=0; i<array.length; i++){
        if(value == array[i]) return true;
    }
    return false;
}

function Loader(v) {
    v = v === undefined ? 1 : v;
	const block = 'loader_block';
	if(!document.getElementById(block)) {
		return false;
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

function CartItemReCount(thisid, id) {
	if(!document.getElementById(thisid)) {
		return false;
	}
	const block = 'cart_product_count_'+id;
	if(!document.getElementById(block)) {
		return false;
	}
	const price = 'cart_product_price_'+id;
	if(!document.getElementById(price)) {
		return false;
	}
	const v = parseFloat(jQuery('#'+thisid).val());
	const price_v = parseFloat(jQuery('#'+price).val());
	if(!price_v) {
		return false;
	}
	
	let total = 0;
	total = price_v * v;
	jQuery('#'+block).text(numberWithSpaces(total.toFixed(2)));
}

function numberWithSpaces(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}