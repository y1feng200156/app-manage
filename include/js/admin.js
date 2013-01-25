

function addtag(inputid, htmlid) {
	var tagtext = document.getElementById(htmlid).innerHTML;
	var tag = document.getElementById(inputid).value;
	
	if(checktag(tag)) {
		newtag = '<input type="button" name="tagnamebtn[]" value="'+tag+'" onclick="deletetag(this)"><input type="hidden" name="tagname[]" id="tagnameid'+tag+'" value="'+tag+'">';
		document.getElementById(htmlid).innerHTML += newtag;
	}
	document.getElementById(inputid).value = '';
}

function addtagname(tagname, htmlid) {
	var tagtext = document.getElementById(htmlid).innerHTML;
	var tag = tagname;
	if(checktag(tag)) {
		newtag = '<input type="button" name="tagnamebtn[]" value="'+tag+'" onclick="deletetag(this)"><input type="hidden" name="tagname[]" id="tagnameid'+tag+'" value="'+tag+'">';
		document.getElementById(htmlid).innerHTML += newtag;
	}
}


function deletetag(thebtn) {
	if(confirm('ȷ��ɾ��TAG: '+thebtn.value+'?')) {
		document.getElementById('tagnameid'+thebtn.value).disabled = true;
		thebtn.disabled = true;
		thebtn.style.display = "none";
	}
}

function checktag(Sting) {

	if(Sting.length < 2 || Sting.length > 10) {
		alert('TAG���Ȳ�����Ҫ��');
		return false;
	}
	//���õķ���
	var compStr = "�������������������������������������ܣޣߣ��������������ۣݣ����������������������������������������ã��������������������������������  ~!@#$%^&*()+={}|[]\\:;\"'<,>?/";
    var length2 = Sting.length;
	
	for (var iIndex=0;iIndex<length2;iIndex++) {
		var temp1 = compStr.indexOf(Sting.charAt(iIndex));
		if(temp1>=0){
			alert('TAG���а����Ƿ��ַ�');
			return false;
		}
	}
	return true;
}

function ShowHide(id) {
	if ( itm = document.getElementById(id) ) {
		if (itm.style.display == 'none'){
			itm.style.display = '';
		} else {
			itm.style.display = 'none';
		}
	}
}

function changevalue(id, value) {
	var obj = document.getElementById(id);
	obj.value = value;
}

function validate(theform) {
	var btn = document.getElementById("thevaluesubmit");
	if(btn) btn.disabled = true;
	
	//����
	var subject = document.getElementById("subject");
	if(subject) {
		if (subject.value.length < 2 || subject.value.length > 80) {
			alert("������ı��ⳤ�Ȳ�����Ҫ��(2~80)������ȷ�ϡ�");
			subject.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}
	
	//����
	var catid = document.getElementById("catid");
	if(catid) {
		if (catid.value<1) {
			alert("��Ϊ������Ϣѡ��һ��ϵͳ����");
			catid.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}

	//�ļ���׺��
	var fileext = document.getElementById("fileext");
	if(fileext) {
		if (fileext.value.length < 1 || fileext.value.length > 15) {
			alert("��������ļ���׺�����Ȳ�����Ҫ������ȷ�ϡ�");
			fileext.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}

	//����
	var name = document.getElementById("name");
	if(name) {
		if (name.value.length < 1 || name.value.length > 50) {
			alert("����������Ƴ��Ȳ�����Ҫ������ȷ�ϡ�");
			name.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}

	//������
	var groupname = document.getElementById("groupname");
	if(groupname) {
		if (groupname.value.length < 1 || groupname.value.length > 50) {
			alert("������������Ƴ��Ȳ�����Ҫ������ȷ�ϡ�");
			groupname.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}

	//�û�����
	var username = document.getElementById("username");
	if(username) {
		if (username.value.length < 1 || username.value.length > 30) {
			alert("��������û����Ƴ��Ȳ�����Ҫ������ȷ�ϡ�");
			username.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}
	
	//��������
	var typename = document.getElementById("typename");
	if(typename) {
		if (typename.value.length < 1 || typename.value.length > 30) {
			alert("����������Ƴ��Ȳ�����Ҫ������ȷ�ϡ�");
			typename.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}
	
	//��������
	var field = document.getElementById("field");
	if(field) {
		if (field.value.length < 1 || field.value.length > 30) {
			alert("����������Ƴ��Ȳ�����Ҫ������ȷ�ϡ�");
			field.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}
	
	//�ռ�����
	var spacename = document.getElementById("spacename");
	if(spacename) {
		if (spacename.value.length < 1 || spacename.value.length > 30) {
			alert("������Ŀռ����Ƴ��Ȳ�����Ҫ������ȷ�ϡ�");
			spacename.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}
	
	//�ռ�����
	var importtext = document.getElementById("importtext");
	if(importtext) {
		if (importtext.value.length < 1 || importtext.value.length > 30) {
			alert("������ĵ����ı����Ȳ�����Ҫ������ȷ�ϡ�");
			importtext.focus();
			if(btn) btn.disabled = false;
			return false;
		}
	}
	
	//ҳ���Զ���
	if(typeof window.thevalidate == 'function') {
		if(thevalidate(theform)) {
			return true;
		} else {
			if(btn) btn.disabled = false;
			return false;
		}
	}

	return true;
}

function listsubmitconfirm(theform) {
	theform.listsubmit.disabled = true;
	if(confirm("��ȷ��Ҫִ�б�������")) {
		theform.listsubmit.disabled = false;
		return true;
	} else {
		theform.listsubmit.disabled = false;
		return false;
	}
}

function getid(id) {
	return document.getElementById(id);
}

function uploadFile(mode) {
	var theform = document.getElementById("theform");
	var msg = document.getElementById("divshowuploadmsg");
	var msgok = document.getElementById("divshowuploadmsgok");
	var oldAction = theform.action;
	var oldonSubmit = theform.onSubmit;
	msgok.style.display = 'none';
	msg.style.display = '';
	msg.innerHTML = "�����ϴ��У������Ե�......";
	theform.action = siteUrl + "/batch.upload.php?mode=" + mode;
	theform.onSubmit = "";
	theform.target = "phpframe";
	theform.submit();
	theform.action = oldAction;
	theform.onSubmit = oldonSubmit;
	theform.target = "";
	if(mode ==2) {
		delpic();
	}
	return false;
}

function attacheditsubmit(aid) {
	var theform = document.getElementById("theform");
	var oldAction = theform.action;
	var oldonSubmit = theform.onSubmit;
	theform.action = siteUrl + "/batch.upload.php?editaid=" + aid;
	theform.onSubmit = "";
	theform.target = "phpframe";
	theform.submit();
	theform.action = oldAction;
	theform.onSubmit = oldonSubmit;
	theform.target = "";
	return false;
}

/**
 * showmenu/hidemenu ����hideshowtagsȡ��
 */
function showmenu(id) {
	var thismenu=document.getElementById(id);
	thismenu.style.display="block";
}
function hidemenu(id) {
	var thismenu=document.getElementById(id);
	thismenu.style.display="none";
}
/**
 * ���ز���ʾ�ϴ�DIV�е�ָ����ǩ
 * @param string id: ����ʾ�ı�ǩID
 */
function hideshowtags(upid,id) {
	//��ȡ�ϴ�DIV����
	var uploadobj = getbyid(upid).getElementsByTagName('div');
	for(var i=0; i< uploadobj.length; i++) {
		if(uploadobj[i].id.indexOf('upload') != -1) {
			uploadobj[i].style.display = "none";
		}
	}
	var showtagobj = getbyid(id);
	showtagobj.style.display = "block";
	
	var tags = getbyid(upid).getElementsByTagName('div')[0].getElementsByTagName('a');
	for(var i=0; i< tags.length; i++) {
		tags[i].className = "";
	}
	if (getbyid(id+'tab')) { getbyid(id+'tab').className = "current"; }
}
/***************�����ϴ����ÿ�ʼ***********************/
/**
 * ����һ��Ԥ��ͼƬ
 * @param object obj: �ϴ��ı������
 */
function insertimg(obj) {
	var childnum = getbyid("batchdisplay").getElementsByTagName("input");	//��ȡ�ϴ����Ƹ���
	var upallowmax = getbyid("uploadallowmax").value;
	//�ж��Ƿ񳬹�����
	if(upallowmax < childnum.length ) {
		alert("һ����������ϴ�"+ upallowmax +"��");
		return false;
	}
	//��ȡ���ID
	var id =getmaxid();
	//���Ĭ�ϵ�DIV
	var pichtml = '';
	var src = obj.value;
	
	//�ж��ļ�����
	var patn = /\.jpg$|\.jpeg$|\.gif$|\.png$|\.bmp$/i;
	var filetype = 'file';
	if(patn.test(src)){
		filetype = 'image';
		
	}
	
	var temp_title = src.substr(src.lastIndexOf('\\')+1);
	if(src.lastIndexOf('.') != -1){
		temp_title = temp_title.substr(0,(src.substr(src.lastIndexOf('\\')+1)).lastIndexOf('.'));
	}
	pichtml += '<div class="picspace" id="pic_space_' + id + '" onmouseover="mouseoverpic(' + id + ', 0)" onmouseout="mouseoutpic(' + id + ', 0)">';
	pichtml += '<a href="javascript:;" onclick="delpic('+id+');" style="float: right; margin: 10px 5px 0 0;">ɾ��</a>';
	if (navigator.userAgent.toLowerCase().indexOf('ie') < 0 || filetype != 'image') {
		pichtml += '<img src="admin/images/upload_file.gif" alt="upload file" class="nopreview" align="absmiddle" />';
	} else {
		pichtml += '<img src="' + src + '" id="pic_item_id_' + id + '" align="absmiddle" />';
	}
	pichtml += '<span class="pictext" id="pic_text_' + id + '">';
	pichtml += '<label id="label_pic_' + id + '" title="'+getStrbylen(temp_title,40)+'"><span id="pic_' + id + '">'+getStrbylen(temp_title,16)+'</span><input  id="pic_input_' + id + '" type="text" name="picname[]" value="'+temp_title+'" style="display: none;" /></label>';
	pichtml += '</span><div style="display:none;"><input type="text" name="title[]" value="'+ temp_title +'" id="pic_title_' + id + '" /><input type="text" name="thumb[]" value="'+ (getbyid('uploadthumb2') == null?"":getbyid('uploadthumb2').value) +'"/></div>';
	pichtml += '</div>';

	// ��ͼƬ��ӵ�pic_space_main��ȥ
	var picmain = getbyid("batchpreview");
	picmain.innerHTML = picmain.innerHTML + pichtml;
	obj.style.display = 'none';	//���ص�ǰ���ϴ�����
	var newid = id+1;	//��ȡ��һ�������ϴ�ID�������µ��ϴ��ؼ�
	addupload(newid);	//����һ���µ��ϴ�����
}
/**
 * ���һ���µ��ϴ�����ؼ�
 * @param int newid: �µ��ϴ��ؼ�ID��׺
 */
function addupload(newid) {
	//�������ɷ�ʽ����������֮��ļ���������
	try{
		//IEģʽ�µĴ�����ʽ,�������setAttribute�������Դ�����һЩδ֪�Ĵ���
		var uploadHTML = document.createElement("<input type=\"file\" id=\"batch_" + newid + "\" name=\"batchfile[]\" onchange=\"insertimg(this)\" class=\"fileinput\">");
		getbyid("batchdisplay").appendChild(uploadHTML);
	}catch(e) {
		//��IEģʽ����Ҫ�����еĳ���setAttribute�������ԣ��������ɵĽ������������ʼ��
		var uploadobj = document.createElement("input");
		uploadobj.setAttribute("name", "batchfile[]");
		uploadobj.setAttribute("onchange", "insertimg(this)");
		uploadobj.setAttribute("type", "file");
		uploadobj.setAttribute("id", "batch_" + newid);
		uploadobj.setAttribute("class", "fileinput");
		getbyid("batchdisplay").appendChild(uploadobj);
	}
}
/**
 * �õ�ҳ���п��õ����ID��
 * д���������Ҫ����Ϊ����ͨ��ͼƬ�ĸ�����������õ����ID��.
 * ͼƬ�ǿ���ȡ����,���ȡ��,��ͨ��ͼƬ������������ID�Ż�������ͼƬ��ID���ص�.
 */
function getmaxid() {
	var items = getbyid("batchdisplay").getElementsByTagName("input");
	var count = items.length;
	var id = 0;
	for (var i=0; i<count; i++) {
		if(items[i].id.substr(0, 6) == "batch_") {
			tmp_id = new Number(items[i].id.substr(6));
			if(id < tmp_id) {
				id = tmp_id;
			}
		}
	}
	if(id == 0) {
		return 1;
	}
	id = new Number(id);
	return id;
}
/**
 * ��ȡָ���ַ�������
 * @param string str: Ҫ��ȡ���ַ���
 * @param int len: Ҫ��ȡ�ĳ���
 * @return 
 */
function getStrbylen(str, len) {
	var num = 0;
	var strlen = 0;
	var newstr = "";
	var laststrlen = 1;
	var obj_value_arr = str.split("");
	for(var i = 0; i < obj_value_arr.length; i ++) {
		if(i < len && num + byteLength(obj_value_arr[i]) <= len) {
			num += byteLength(obj_value_arr[i]);
			strlen = i + 1;
		}
	}
	if(str.length > strlen) {
		if(byteLength(str.charAt(strlen-1)) == 1){
			laststrlen = 2;
		}
		newstr = str.substr(0, strlen-laststrlen) + '��';
	} else {
		newstr = str;
	}
	return newstr;
}
/**
 * �ж���Ӣ�ʻ���ʱ��ĳ���
 * @param string sStr: ���ŵ��ַ���
 */
function byteLength (sStr) {
	aMatch = sStr.match(/[^\x00-\x80]/g);
	return (sStr.length + (! aMatch ? 0 : aMatch.length));
}
/**
 * ����Ƶ�Ԥ��ͼ�¼�
 * @param int id: ��������ID��׺
 * @param int optype: ��������
 */
function mouseoverpic(id,optype) {
	if(optype == 1) {
		var delpicbutton = getbyid("del_pic_button" + id);
		if(delpicbutton.style.display != "inline") {
			delpicbutton.style.display="inline";
		}
	} else if(optype == 0) {
		getbyid("pic_" + id).style.display = "none";
		getbyid("pic_input_" + id).style.display = "inline";
	}
}
/**
 * ����Ƴ�Ԥ��ͼ�¼�
 * @param int id: ��������ID��׺
 * @param int optype: ��������
 */
function mouseoutpic(id, optype) {
	if(optype == 1) {
		var delpicbutton = getbyid("del_pic_button" + id);
		delpicbutton.style.display="none";
	} else if(optype == 0) {
		var picobj = getbyid("pic_" + id);
		var inputobj = getbyid("pic_input_" + id);
		var labelobj = getbyid("label_pic_" + id);
		picobj.style.display = "inline";
		inputobj.style.display = "none";
		//�ж��Ƿ�Ϊ�գ����Ϊ����ȡĬ�ϵ��ļ�����
		var re = /\s/ig;
   		var result = inputobj.value.replace(re, "");
		if(result == "") {
			inputobj.value = getbyid("pic_title_" + id).value;
		}
		picobj.innerText = inputobj.value;
		labelobj.title = inputobj.value;
	}
}
/**
 * ɾ��ȫ��Ԥ��ͼ�����е�ĳһ��
 * @param int pid: Ҫɾ����Ԥ��ͼID��׺����û����д��ɾ��ȫ����Ԥ��ͼ
 */
function delpic(pid) {
	//�ж��Ƿ��д�ɾ����ͼƬID�����û����ɾ��ȫ����ͼƬ
	if(typeof pid != "undefined") {
		getbyid("batchpreview").removeChild(getbyid("pic_space_"+pid));
		//ɾ�����Ӧ���ϴ��ؼ�
		getbyid("batchdisplay").removeChild(getbyid("batch_" + pid));
	} else {
		//��ȡȫ��ͼƬ����
		var previewobj = getbyid("batchpreview");
		var allobj = previewobj.getElementsByTagName("div");
		for(var i = allobj.length - 1; 0<=i; i--) {
			if(allobj[i].id.indexOf("pic_space_") != -1) {
				previewobj.removeChild(allobj[i]);
			}
		}
		var bdisplay = getbyid("batchdisplay");
		var allupobj = bdisplay.getElementsByTagName("input");
		for(var i = allupobj.length - 1; 0<=i; i--) {
			bdisplay.removeChild(allupobj[i]);
		}
		addupload(1);
	}
	return false;
}
/***************�����ϴ����ý���***********************/
//��ʾ������
function hidetoolbar() {
	window.parent.document.getElementById("toolbarframe").style.display="none";
}
function hidetoolbarOpera() {
	if (navigator.userAgent.toLowerCase().indexOf('opera') > -1) {
		window.parent.document.getElementById("toolbarframe").style.display="none";
	}
}

function showtoolbar() {
	document.getElementById("toolbarframe").style.display = "block";
}

//�л�Ƶ��
function channelNav(Obj, channel) {
	var channelTabs = document.getElementById('topmenu').getElementsByTagName('a');
	for (i=0; i<channelTabs.length; i++) {
		channelTabs[i].className = '';
	}
	Obj.className = 'current';
	Obj.blur();
	var sideDoc = window.parent.leftframe.document;
	var sideChannels = sideDoc.getElementsByTagName('div')
	for (i=0; i<sideChannels.length; i++) {
		sideChannels[i].style.display = '';
	}
	var sideChannelLinks = sideDoc.getElementsByTagName('a')
	for (i=0; i<sideChannelLinks.length; i++) {
		sideChannelLinks[i].className = '';
	}
	var targetChannels = channel.split(',');
	sideDoc.getElementById(targetChannels[0]).getElementsByTagName('a')[0].className = 'current';
	for (i=0; i<targetChannels.length; i++) {
		sideDoc.getElementById(targetChannels[i]).style.display = 'block';
	}
}


//���β˵�
function treeView() {
	var list = document.getElementsByTagName('div');

	for ( i=0; i<list.length; i++ ) {
		list[i].getElementsByTagName('h3')[0].onclick = function() {
			if (this.parentNode.getElementsByTagName('ul')[0].style.display == '') {
				this.parentNode.getElementsByTagName('ul')[0].style.display = 'none';
			} else {
				this.parentNode.getElementsByTagName('ul')[0].style.display = '';
			}
		}
	}
	
	var linkitem = document.getElementsByTagName('a');
	for ( j=0; j<linkitem.length; j++ ) {
		linkitem[j].onclick = function() {
			for ( k=0; k<linkitem.length; k++ ) {
				linkitem[k].className = '';
			}
			this.className = 'current';
			this.blur();
		}
	}
}

//��������
function sideSwitch() {
	var mainFrame = window.parent.document.getElementById('mainframeset');
	var switcher = document.getElementById('sideswitch');
	if (mainFrame.cols == '0,*') {
		mainFrame.cols = '200,*';
		switcher.innerHTML = '�رղ���';
		switcher.className = 'opened';
	} else {
		mainFrame.cols = '0,*';
		switcher.innerHTML = '�򿪲���';
		switcher.className = 'closed';
	}
}

function robotReferUrl(mode) {
	var theform = document.getElementById("theform");
	var msg = document.getElementById("divshowrobotmsg");
	var msgok = document.getElementById("divshowrobotmsgok");
	var oldAction = theform.action;
	var oldonSubmit = theform.onSubmit;
	msgok.style.display = 'none';
	msg.style.display = '';
	msg.innerHTML = "���ڴ�ȡ�У������Ե�......";
	theform.action = siteUrl + "/batch.common.php?action=" + mode;
	theform.onSubmit = "";
	theform.target = "phpframe";
	theform.submit();
	theform.action = oldAction;
	theform.onSubmit = oldonSubmit;
	theform.target = "";

	return false;
}