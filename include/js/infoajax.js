var num_item = 0;
var bool_ext = true;
function setitem(){
	objudiv = document.getElementById('udiv');
	adddiv = document.getElementById('url');

	if(adddiv.value != ''){
		urln = document.createElement('div');
		urln.id = 'url_' + num_item;
		
		urln.appendChild(document.createTextNode(adddiv.value));
		urln.appendChild(document.createTextNode(' '));

		oBody = document.createElement('a');
		oBody.href = '#';
		oBody.title = '';
		if(window.document.all != null) // IE
			oBody.attachEvent('onclick', new Function('delitem('+num_item+');'));
		else // Firefox
			oBody.addEventListener('click', new Function('delitem('+num_item+');'), false);
		oBody.appendChild(document.createTextNode('ɾ��'));
		urln.appendChild(oBody);
		
		oBody = document.createElement("input");
		oBody.id = 'referurl[]';
		oBody.name = 'referurl[]';
		oBody.type = 'text';
		oBody.value = adddiv.value;
		oBody.size = '5';
		oBody.style.display = 'none';
		urln.appendChild(oBody);

		objudiv.appendChild(urln);
		adddiv.value = '';
		num_item++
	}
}
function delitem(n){
	choosemode(1);
	objudiv = document.getElementById('udiv');
	objudiv.removeChild(document.getElementById('url_'+n));

}
function insertitem(){
	choosemode(1);
	setitem();
}
function choosemode(n){
	objmode = document.getElementsByName('mode');
	if(n == 1){
			objmode[0].checked = true;
			modedisabled('m2', true);
			modedisabled('m1', false);
	} else {
			objmode[1].checked = true;
			modedisabled('m1', true);
			modedisabled('m2', false);
	}
}
function modedisabled(n, b){
	objmid = document.getElementById(n);
	midinput = objmid.getElementsByTagName('input');
	for(i = 0; i < midinput.length; i++){
		if(midinput[i].id != 'mode')
			midinput[i].disabled = b;
	}
	if(b == true)
		objmid.style.color = '#aaa';
	else
		objmid.style.color = '';
}
function wildcard(n) {
	obj_wildcard = document.getElementById('wildcard');
	obj_from = document.getElementById('from');
	obj_to = document.getElementById('to');
	obj_from.value = obj_to.value = '';
	document.getElementById('str_wc').style.display = 'none';
	if(n == 0){
		obj_wildcard.style.display = '';
		obj_from.maxLength = obj_to.maxLength = 10;
	} else {
		obj_wildcard.style.display = 'none';
		obj_from.maxLength = obj_to.maxLength = 1;
	}
}
function extractmessages() {
	if(bool_ext) {
		robotid = document.getElementById('robotid').value;
		getdata('item[]', siteUrl + '/admincp.php?action=extractmessages', robotid);
	} else {
		return false;
	}
}
function extractpause(obj) {
	bool_ext = !bool_ext;
	if(bool_ext) extractmessages();
	obj.value = bool_ext ? '��ͣ�ɼ�' : '�����ɼ�';
}

function str_pad_left(input, pad_length){
	var pad_length = parseInt(pad_length);
	
	if(pad_length > input.length){
			input = (Math.pow(10, pad_length) + parseInt(input)) + '';
			input = input.substring(1, input);
	}
	return input;
}

function inputcheck(obj){
	var obj_referurl2 = document.getElementById('referurl2');
	var obj_from = document.getElementById('from');
	var obj_to = document.getElementById('to');
	var obj_str_wc = document.getElementById('str_wc');
	var obj_type = document.getElementsByName('type');
	var obj_len = document.getElementById('len');
	var str_from = obj_from.value;
	var str_to = obj_to.value;
	var str_wc = '';

	obj_referurl2.value = obj_referurl2.value.replace(/\*+/g, '*');
	if(obj.id != 'referurl2'){
		str_replace = obj_type[0].checked == true ? /\D+/g : /[^a-z]/gi; 
		obj.value = obj.value.replace(str_replace, '');
	}
	if(obj_referurl2.value.search(/\*/g) >= 0 && obj_from.value != '' && obj_to.value != ''){
		if(obj_type[0].checked == true){
			str_from = str_pad_left(obj_from.value, obj_len.value);
			str_to = str_pad_left(obj_to.value, obj_len.value);
		}
		str_wc = obj_referurl2.value.replace(/\*/, str_from) + '<br />......<br />' + obj_referurl2.value.replace(/\*/, str_to);
		obj_str_wc.innerHTML = str_wc;
		obj_str_wc.style.display = '';
	} else {
		obj_str_wc.style.display = 'none';
	}
}

function initxmlhttp() {
	var xmlhttp;
	try {
		xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
			xmlhttp=false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		try {
			xmlhttp = new XMLHttpRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}
	if (!xmlhttp && window.createRequest) {
		try {
			xmlhttp = window.createRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}
	return xmlhttp;
}

function getdata(formbody, posturl, robotid) {
	var xmlhttp = initxmlhttp();
	
	robotid = robotid == null ? 0 : parseInt(robotid);
	var poststr = '';
	var bool_up = 0;
	var obj_input = document.getElementsByName(formbody);
	var obj_ext_id = obj_ext_tr = obj_ext_href = obj_ext_return = obj_ext_a = '';
	var obj_extps = document.getElementById('extps');
	var obj_message = document.getElementById('message');
	
	for(i = 0; i < obj_input.length && i < 5; i++) {
		obj_ext_return = document.getElementById('ext_return_' + obj_input[i].value);
		obj_ext_tr = document.getElementById('ext_tr_' + obj_input[i].value);		
		
		if(obj_input[i].disabled == false) {
			if(obj_input[i].checked == true) {
				if(bool_up == 0) {
					obj_ext_href = document.getElementById('ext_href_' + obj_input[i].value);
					obj_ext_id = obj_input[i].value;
					obj_ext_a = document.getElementById('ext_a_' + obj_input[i].value);

					obj_ext_return.innerHTML = '�ɼ�������...';
					obj_extps.style.display = '';
					obj_message.innerHTML = '���ڲɼ���' + obj_ext_href.value + '...';
					poststr += '&' + formbody + '=' + obj_ext_href.value;
					
					obj_ext_tr.removeChild(obj_input[i]);
					i--;
					bool_up++;
				} else {
					obj_ext_return.innerHTML = '<font style="color: #777">�ȴ���...</font>';
				}
			} else {
				obj_ext_return.innerHTML = '<font style="color: #ccc">���ɼ�</font>';
				obj_ext_tr.removeChild(obj_input[i]);
				i--;
			}
		}
	}

	if(poststr == '') {
		obj_extps.style.display = 'none';
		if(robotid != 0) {
			obj_message.innerHTML = '<a href="' + siteUrl + '/admincp.php?action=robotmessages&robotid='+robotid+'">�ɼ���ϣ�����鿴�ɼ����</a>';
		} else {
			obj_message.innerHTML = '�ɼ������������޷��鿴�ɼ������';
		}
		return false;
	}
	
	poststr += '&robotid=' + robotid;
	xmlhttp.open('POST', posturl, true);
	xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlhttp.send(poststr);
	
	xmlhttp.onreadystatechange = function() {
		if(xmlhttp.readyState==4) {
			obj_ext_return = document.getElementById('ext_return_' + obj_ext_id);
			
			if(document.getElementById('follow').checked == true) {
				window.location.href = '#' + obj_ext_a.name;
			}
			if(xmlhttp.status==200){
				var re = xmlhttp.responseText;
				re = re.split('!|!');
				obj_ext_return.innerHTML = '<font style="color: #0F0">�ɼ��ɹ�</font> <a href="' + siteUrl + '/admincp.php?action=robotmessages&op=viewmessage&itemid='+re['1']+'" target="_blank">�鿴</a>';
			} else {
				obj_ext_return.innerHTML = '<font style="color: #F00">�ɼ�ʧ��</font>';
			}
			extractmessages();
		}
	}
}