var clientForm = null;
var uploadServ = null;
var fileLength = 0;
var fileext = null;
var uploadLength = 0;
var proInter;
var step=1;
var HttpRequest = null;

function Init(obj) {
	if(
	   	confirm("�ϴ�׼����ʼ���������Ҫ�ϳ���һ��ʱ�������\n���ϴ������У��벻Ҫ�رջ���ˢ�±�ҳ�棬���������ϴ�ʧ��\n��ֻ��Ҫ���ĵȺ򼴿�")) {
		var uploadFile = document.getElementById('xspace-process');
		uploadFile.style.display = 'none';
		var jsFrame = document.createElement("iframe");
		jsFrame.id = "jsFrame";
		jsFrame.width = 0;
		jsFrame.height = 0;
		jsFrame.marginwidth = 0;
		jsFrame.frameborder = 0;
		jsFrame.src = siteUrl + '/batch.common.php?action=process&tmp='+Math.random();
		getbyid("uploadvideo").appendChild(jsFrame);
		var uploadInfo = document.getElementById('xspace-processinfo');
		uploadInfo.style.display = 'block';
		uploadInfo.innerHTML = "��ʼ׼���ϴ��������ĵȺ�...";
		clientForm = obj;
	}
	return false;
}
function setServer() {
	document.getElementById('uploadFrame').src = siteUrl + '/spacecp.php?action=spacevideos&op=setServer&tmp='+Math.random();
}
function setUpload(srvAddr) {
	if(uploadServ == null) {
		uploadServ = srvAddr;
		parent.document.getElementById('uploadFrame').src = "about:blank";
		parent.clientForm.setAttribute('action', 'http://'+srvAddr);
		parent.clientForm.submit();
		step = 2;
	} else {
		step = 2;
	}
}
function setFileInfo() {
	step = 3;
}
function gotoEnd() {
	clearInterval(proInter);
	parent.document.getElementById("uploadFrame").src="about:blank";
	parent.document.getElementById("phpframe").src="about:blank";
	parent.document.getElementById('xspace-processinfo').innerHTML = "�����ˣ��ϴ�ʧ�ܣ��볢�������ϴ�";
}

function Process() {
	if(Number(uploadLength) < Number(fileLength)) {
		parent.document.getElementById('xspace-processinfo').innerHTML = "<div style='text-align: center;'><p>���ϴ� "+uploadLength+"B / �� "+fileLength+"B</p><div style='margin: 0 auto; width: 400px; height: 22px; background: #F8F5E5; border: 1px solid #EBE7D3; text-align: left'><div style='height: 22px; background: #F6AB20; width:" +(parseInt(uploadLength)/parseInt(fileLength))*100 + "%; text-align: center;'></div></div>";
	} else {
		step = 4;
		parent.document.getElementById('fileext').value = fileext;
		parent.document.getElementById('theform').target="phpframe";

		clearInterval(proInter);
		parent.document.getElementById("uploadFrame").src="about:blank";
		parent.document.getElementById("phpframe").src="about:blank";

		parent.document.getElementById('xspace-processsubmit').style.display = 'block';	
		parent.document.getElementById('uploadvideo').style.display = 'none';
	
	}
}

function send() {
	var uAjax = null;
	uAjax = new Ajax('uploadid', 'HTML');
	uAjax.get(siteUrl + '/batch.common.php?action=uploadx&step='+step+'&tmp='+Math.random(), function(s) {  if(s != "") {eval(s);}});
}