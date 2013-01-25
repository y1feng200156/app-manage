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
	   	confirm("上传准备开始，这可能需要较长的一段时间来完成\n在上传过程中，请不要关闭或者刷新本页面，否则会造成上传失败\n您只需要耐心等候即可")) {
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
		uploadInfo.innerHTML = "开始准备上传，请耐心等候...";
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
	parent.document.getElementById('xspace-processinfo').innerHTML = "出错了，上传失败，请尝试重新上传";
}

function Process() {
	if(Number(uploadLength) < Number(fileLength)) {
		parent.document.getElementById('xspace-processinfo').innerHTML = "<div style='text-align: center;'><p>已上传 "+uploadLength+"B / 共 "+fileLength+"B</p><div style='margin: 0 auto; width: 400px; height: 22px; background: #F8F5E5; border: 1px solid #EBE7D3; text-align: left'><div style='height: 22px; background: #F6AB20; width:" +(parseInt(uploadLength)/parseInt(fileLength))*100 + "%; text-align: center;'></div></div>";
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