toggleKey =new Object();	//��ť״̬����
toggleKey[0] = "_off";
toggleKey[1] = "_on";
toggleKey[2] = "_ovr";
toggleKey[3] = "_out";
toggleKey[4] = "_mdn";
toggleKey[5] = "_mup";
toggleKey[6] = "_buf";
toggleKey[7] = "_onovr";
toggleKey[8] = "_offovr";
toggleKey[9] = "_etc";

if(document.images) {	//��ťͼ�������̬
	img = new Object();
	
	img.pmode_off = new Image();
	img.pmode_off.src = siteUrl+"/images/base/music/btn_rndmode_off.gif";
	img.pmode_on = new Image();
	img.pmode_on.src = siteUrl+"/images/base/music/btn_rndmode_on.gif";
	img.pmode_ovr = new Image();
	img.pmode_ovr.src = siteUrl+"/images/base/music/btn_rndmode_ovr.gif";

	img.tloop_off = new Image();
	img.tloop_off.src = siteUrl+"/images/base/music/btn_trkloop_off.gif";
	img.tloop_on = new Image();
	img.tloop_on.src = siteUrl+"/images/base/music/btn_trkloop_on.gif";
	img.tloop_ovr = new Image();
	img.tloop_ovr.src = siteUrl+"/images/base/music/btn_trkloop_on.gif";

	img.rept_off = new Image();
	img.rept_off.src = siteUrl+"/images/base/music/btn_rept_off.gif";
	img.rept_on = new Image();
	img.rept_on.src = siteUrl+"/images/base/music/btn_rept_on.gif";
	img.rept_ovr = new Image();
	img.rept_ovr.src = siteUrl+"/images/base/music/btn_rept_ovr.gif";

	img.playt_off = new Image();
	img.playt_off.src = siteUrl+"/images/base/music/btn_play.gif";
	img.playt_on = new Image();
	img.playt_on.src = siteUrl+"/images/base/music/btn_play_on.gif";
	img.playt_ovr = new Image();
	img.playt_ovr.src = siteUrl+"/images/base/music/btn_play_ovr.gif";
	img.playt_buf = new Image();
	img.playt_buf.src = siteUrl+"/images/base/music/btn_play_buf.gif";

	img.pauzt_off = new Image();
	img.pauzt_off.src = siteUrl+"/images/base/music/btn_pauz_off.gif";
	img.pauzt_on = new Image();
	img.pauzt_on.src = siteUrl+"/images/base/music/btn_pauz_on.gif";
	img.pauzt_ovr = new Image();
	img.pauzt_ovr.src = siteUrl+"/images/base/music/btn_pauz_ovr.gif";

	img.stopt_off = new Image();
	img.stopt_off.src = siteUrl+"/images/base/music/btn_stop.gif";
	img.stopt_on = new Image();
	img.stopt_on.src = siteUrl+"/images/base/music/btn_stop_on.gif";
	img.stopt_ovr = new Image();
	img.stopt_ovr.src = siteUrl+"/images/base/music/btn_stop_ovr.gif";

	img.rwdt_off = new Image();
	img.rwdt_off.src = siteUrl+"/images/base/music/btn_rwd.gif";
	img.rwdt_on = new Image();
	img.rwdt_on.src = siteUrl+"/images/base/music/btn_rwd_on.gif";
	img.rwdt_ovr = new Image();
	img.rwdt_ovr.src = siteUrl+"/images/base/music/btn_rwd_ovr.gif";

	img.fwdt_off = new Image();
	img.fwdt_off.src = siteUrl+"/images/base/music/btn_fwd.gif";
	img.fwdt_on = new Image();
	img.fwdt_on.src = siteUrl+"/images/base/music/btn_fwd_on.gif";
	img.fwdt_ovr = new Image();
	img.fwdt_ovr.src = siteUrl+"/images/base/music/btn_fwd_ovr.gif";

	img.prevt_out = new Image();
	img.prevt_out.src = siteUrl+"/images/base/music/btn_prev.gif";
	img.prevt_ovr = new Image();
	img.prevt_ovr.src = siteUrl+"/images/base/music/btn_prev_ovr.gif";

	img.nextt_out = new Image();
	img.nextt_out.src = siteUrl+"/images/base/music/btn_next.gif";
	img.nextt_ovr = new Image();
	img.nextt_ovr.src = siteUrl+"/images/base/music/btn_next_ovr.gif";

	img.vmute_off = new Image();
	img.vmute_off.src = siteUrl+"/images/base/music/btn_mute_off.gif";
	img.vmute_on = new Image();
	img.vmute_on.src = siteUrl+"/images/base/music/btn_mute_on.gif";
	img.vmute_ovr = new Image();
	img.vmute_ovr.src = siteUrl+"/images/base/music/btn_mute_ovr.gif";
	
	img.screen_off = new Image();
	img.screen_off.src = siteUrl+"/images/music/btn_fullscreen.gif";
	img.screen_on = new Image();
	img.screen_on.src = siteUrl+"/images/music/btn_fullscreen_ovr.gif";


}

function imgChange(id, act){
	if(document.images) {
		document.images[id].src = eval("img."+ id + toggleKey[act] + ".src");
	}
}

function imgtog(tg, act) {
	switch(tg) {
		case 'vmute': act=="2"?imgChange("vmute", 2):imgmute();break;
		case 'pmode': act=="2"?imgChange("pmode", 2):imgrnd();break;
		case 'rept': act=="2"?imgChange("rept", 2):imgrept();break;
		case 'nextt': act=="2"?imgChange("nextt", 2):imgChange("nextt",3);break;
		case 'prevt': act=="2"?imgChange("prevt", 2):imgChange("prevt",3);break;
		case 'pauzt': act=="2"?imgpauz(2):imgpauz();break;
		case 'playt': act=="2"?imgplay(2):imgplay();break;
		case 'stopt': act=="2"?imgstop(2):imgstop();break;
		case 'plist': act=="2"?imgChange("plist", 2):imgChange("plist",3);break;
		case 'tloop': act=="2"?imgChange("tloop", 2):imgtloop("plist",3);break;
		case 'screen': act=="0"?imgChange("screen", 0):imgChange("screen",1);break;
		default: break;			
	}
}

function imgmute() {
	var ps=musicobj.settings;
	ps.mute?imgChange("vmute",1):imgChange("vmute",0);
}

function imgrnd() {
	blnRndPlay?imgChange("pmode", 1):imgChange("pmode", 0);
}

function imgrept() {
	blnRept?imgChange("rept", 1):imgChange("rept", 0);
}

function imgpauz(f) {
	
	if(f==2) {
		imgChange("pauzt",2);
	} else { 
		imgChange("pauzt",0);
	}
}

function imgplay(f) {
	if(f==2) { 
		imgChange("playt",2);
	} else {
		imgChange("playt",1);
	}
}



function imgstop(f){
 	if(f==2) { 
 		imgChange("stopt",2);
 	} else { 
 		imgChange("stopt",0);
	}
}

function imgtloop() {
	blnLoopTrk ? imgChange("tloop", 1):imgChange("tloop", 0);
}

function flashList(){
	musicList = document.getElementById('xspace-mmList');
	if(intMmCnt >0 ){
		var list_num=0;
		document.getElementById('xspace-mmList').innerHTML='';
		for (var i=0; i < intMmCnt; i++) {
			list_num = i + 1;
			if(objMmInfo[i].selMm=="t") {
				elm='<input type=checkbox  style="cursor:pointer;" onClick=chkItemfSel('+ i +'); checked>' ;
			} else {
				elm = ' <input type=checkbox style="cursor:pointer;" onClick=chkItemfSel('+ i +');>' ;
			}
			elm = elm + '&nbsp;' + list_num + '. ' 
			elm = elm + '<a href=javascript:selPlaySWF(' + i + ');'
			elm = elm + ' onclick=\"this.blur();\">'
			if(objMmInfo[i].mmTit =="nAnT") {
				elm = elm + "(Info)Tracing Media Author-Titles";
			} else {
				elm = elm + objMmInfo[i].mmTit;
			}
			elm= elm+  '</a><br>';
			musicList.innerHTML=musicList.innerHTML+elm;
		}
	} else { musicList.innerHTML='<div align=center> �Բ���û���ҵ��κ�ý���ļ� </div>'; }
}

var movie,timer,step,total,state,delay=1000,qtimer,left,osrc,tcount,frameCount,barobj;
autoStart = true;	//�Զ����ţ��������������������ͣ������Ҫ���µ������
/**
 * ��ʼ��Flash������
 */
function initFlash() {
	movie = getbyid('flashplay');
	frameCount = getbyid('caption');
	dTitle = getbyid('disp1');
	barobj = getbyid('bar');
	
	var re = new RegExp("(msie[^;^)^(]*)", "ig");
	if(navigator.userAgent.search(re) == -1) {
		getbyid('control').style.display = 'none';
		movie.childNodes[1].value = movie.childNodes[5].src = urlarr[0][0];
		alert("��ʹ��IE��������ʣ������޷�ʹ��Flash���������ܣ�");
		return false;
	}
	mkfSel();
	if(autoStart) {startplay();}
}
/**
 * ������ѡȡ������Ŀ(Selected Media)������
 */
function mkfSel() {
	intSelMmCnt=0;
	var selidx = 0;

	if(intMmCnt<=0){intSMusicStat=1; blnEnabled=false; return;} //�յĲ����б�

	arrSelMm=new Array();
	for(var i=0; i<intMmCnt;i++) {
		if(objMmInfo[i].selMm =="t") {
			arrSelMm[selidx]=i;
			selidx=selidx+1;
		}
	}
	intSelMmCnt=arrSelMm.length;

	if(intSelMmCnt<=0) {
		blnEnabled=false;
		intSMusicStat=2;
		arrSelMm=null;
		return;
	} else{
		blnEnabled=true;
		mkAct();	//������б�
	} 
}
function init() {
	step = 5;
	state = null;
	total = movie.TotalFrames;
	left=0;
	bar.outerHTML = '<img onmousedown="Down()" onmousemove="Move()" onmouseup="Up()" src="'+siteUrl+'/images/base/music/posbar.gif" width="29" height="10" id="bar" style="position:absolute;left:0;top:0;">';
	
	timer = window.setInterval(showStatus,delay);
}

function loadSWF(src,w,h) {
	if(src=="") return false;
	window.clearInterval(timer);
	window.clearTimeout(qtimer);
	movie.movie = src;
	movie.width = w;
	movie.height = h;
	loaded();
}

function loaded() {
	frameCount.innerText = movie.PercentLoaded()+"%";
	bar.style.width = frameCount.innerText;
	movie.PercentLoaded() == 100 ? init() : window.setTimeout(loaded,100);
}

function startplay() {
	if(blnEOT) { mkSelAct(); }
	if(isNaN(intSupremeTrkIdx) || intSupremeTrkIdx >= intMmCnt) { intSupremeTrkIdx=0; }
	if(blnRndPlay) {
		rndFPlay();
	} else {
		if(objMmInfo[intSupremeTrkIdx].selMm=="t" && objMmInfo[intSupremeTrkIdx].actMm=="t") {
			intCurrActIdx=intSupremeTrkIdx;
			selPlaySWF(intSupremeTrkIdx);
		} else {
			intCurrActIdx=arrActMm[0];
			selPlaySWF(intCurrActIdx);
		}
	}
}
/**
 * �������(Random Play)ģʽ
 */
function rndFPlay(){
	if(blnAbs1stTrack){
		if(objMmInfo[intSupremeTrkIdx].selMm=="t" && objMmInfo[intSupremeTrkIdx].actMm=="t") {
			intCurrActIdx=intSupremeTrkIdx;
			selPlaySWF(intSupremeTrkIdx);
		} else {
			var idx=Math.floor(Math.random() * intActMmCnt);
		 	intCurrActIdx= arrActMm[idx];
			selPlaySWF(intCurrActIdx);
		}
 	} else {
		var idx=Math.floor(Math.random() * intActMmCnt);
	 	intCurrActIdx= arrActMm[idx];
		selPlaySWF(intCurrActIdx);
	} 
}
function selPlaySWF(idx){
	clearTimeout(timer);
	blnExtMmUsed=false;
	intCurrActIdx=idx;
 	var trknum=idx+1;
 	var ctit =objMmInfo[idx].mmTit;
 	if(ctit=="nontitle") {ctit="�������ض�������";}
 	if(blnUseSmi){musicobj.ClosedCaption.SAMIFileName = objMmInfo[idx].mmSmi;}
 	strCurrMediaUrl=objMmInfo[idx].mmUrl;
	//�������·��Ϊ�գ��򲥷���һ��ý��
 	if(strCurrMediaUrl=="" || strCurrMediaUrl==null){ etcMsg(4);setTimeout('nextPlayAuto()', 3000);return; }
 	if(blnAbs1stTrack && intCurrActIdx==intSupremeTrkIdx) {
 		strCurrActTit="[SupremeTrack:" + trknum + "] " + ctit;
 	} else { strCurrActTit= " "+ trknum + ". " + ctit; }
	dTitle.innerHTML = strCurrActTit;
	bar.outerHTML = "<div id=\"bar\" style=\"width:0%;font:3px;height:3px;background:#00FF44\">";
	osrc = strCurrMediaUrl;
	loadSWF(strCurrMediaUrl, movie.width, movie.height);
	if(blnStatusBar) { window.status=(strCurrActTit); }
	chkItemAct(intCurrActIdx); 
}
/**
 * �Զ�������һ������
 */
function nextPlayAuto() {
	if(blnEOT) {
		if(blnLoopTrk) {
			startplay();
		} else { Stop(); }
 	} else {
		if(blnRndPlay) {
			rndFPlay();
		} else {
			intErrCnt=0;
			var idx=intCurrActIdx;
	 		var blnFind=false;
			for(var i=0;i<intSelMmCnt;i++) {
				if(intCurrActIdx==arrSelMm[i]) {
					idx=i+1;
					blnFind=true;
				}
			}
			if(!blnFind) { return; }
			if(idx>=intSelMmCnt) {
				idx=0;
				intCurrActIdx=arrSelMm[idx];
			} else { intCurrActIdx=arrSelMm[idx]; }
			selPlaySWF(intCurrActIdx); 
		}

	}
}
/**
 * ������һ������
 */
function fplayPrev() {
	blnLonelyPlay=false;
	if(!blnEnabled) { waitMsg();return; }
	if(blnEOT) { mkSelAct(); }
	intErrCnt=0;
	if(blnRndPlay) {
		rndFPlay();
	} else {
		var idx=intCurrActIdx;
 		var blnFind=false;
		for(var i=0;i<intSelMmCnt;i++) {
			if(intCurrActIdx==arrSelMm[i]) {
				idx=i-1;
				blnFind=true;
			}
		}
		if(!blnFind) { startplay();return; }
		if(idx<0) {
			idx=intSelMmCnt-1;
			intCurrActIdx=arrSelMm[idx];
		} else { intCurrActIdx=arrSelMm[idx]; }
		selPlaySWF(intCurrActIdx);
	}
}
/**
 * ������һ������
 */
function fplayNext(){
	blnLonelyPlay=false;
 	if(blnEOT){mkSelAct();}
	intErrCnt=0;
	if(blnRndPlay) {
		rndFPlay();
	} else{
		var idx=intCurrActIdx;
 		var blnFind=false;
		for(var i=0;i<intSelMmCnt;i++) {
			if(intCurrActIdx==arrSelMm[i]) {
				idx=i+1;
				blnFind=true;
			}
		}
		if(!blnFind) { startplay();return; }
		if(idx>=intSelMmCnt) {
			idx=0;
			intCurrActIdx=arrSelMm[idx];
		} else {
			intCurrActIdx=arrSelMm[idx];
		}
		selPlaySWF(intCurrActIdx);
	}
}
/**
 * ������һ֡
 */
function Rewind() {
	movie.Rewind();
}

function Back() {
	if(movie.FrameNum >= step && state != "forward") {
		state = "back";
		Quick();
		qtimer = window.setTimeout(Back,delay);
	}
}

function Play() {
	if(movie.movie == "about:blank" || movie.movie == "") startplay();
	movie.Play();
}
/**
 * ��ͣ/���ŵ�ǰӰƬ
 */
function Pause() {
	with(movie)IsPlaying()?StopPlay():Play();
}
/**
 * ֹͣ����
 */
function Stop() {
	movie.StopPlay();	//ֹͣ����
	Rewind();	//���ص�һ֡
}

function Forward() {
	if(movie.FrameNum!=total-1 && state!="back") {
		state = "forward";
		Quick();
		qtimer = window.setTimeout(Forward,delay);
	}
}

function GoToEnd() {
	movie.GoToFrame(total-1);
}

function Replay() {
	Rewind();
	Play();
	window.setTimeout(Play,delay*5);
}

function showStatus() {
	var N = movie.FrameNum;
	frameCount.innerText = (N+1)+"/"+total;
	bar.style.posLeft = parseInt(310*(N+1)/total)|0;
	if((N+1) == total) {
		nextPlayAuto();
	}
}

/**
 * ѭ������
 */
function fLoop(){
	if(blnLoopTrk) {
		blnLoopTrk=false;
		imgChange('tloop',0);
		frameCount.innerHTML="��ѭ������";
	} else {
		blnLoopTrk=true;
		imgChange('tloop',1);
		frameCount.innerHTML="ѭ������";
	}
	tidMsg=setTimeout('showStatus()',1000); 
}
function Quick() {
	var targetFrame = movie.FrameNum+step*{back:-1,forward:1}[state];
	 movie.GoToFrame(targetFrame);
}
function Jump(n) {
	event.cancelBubble = true;
	movie.GoToFrame(n);
	Play();
}
//�����϶�����
var down = false,ox,cx;
/**
 * ��갴��
 */
function Down() {

	window.clearInterval(timer);
	event.cancelBubble=true;
	down=true;
	ox=event.offsetX;
	cx=document.body.scrollLeft+event.x;
	bar.setCapture();
}
/**
 * ����϶�����
 */
function Move() {
	if(down && event.x>left+ox && event.x<left+320+ox) {
		Jump(total*(event.x-left)/330|0);
		bar.style.posLeft=document.body.scrollLeft+event.x-ox;
	}
}
/**
 * �ɿ����ֹͣ��������
 */
function Up() {
	down=false;
	bar.releaseCapture();
	timer = window.setInterval(showStatus,delay);
}
/**
 * ȫ��ѡȡ���еĲ����嵥��Ŀ
 */
function fchkAllSel() {
	for(var i=0; i<intMmCnt; i++){
		objMmInfo[i].selMm="t";
		objMmInfo[i].actMm="t";
	}
	mkfSel();
	flashList();
}

/**
 * ȡ�����е�ý��ѡ��
 */
function fchkAllDesel() {
	for(var i=0; i<intMmCnt; i++){
		objMmInfo[i].selMm="f";
		objMmInfo[i].actMm="f";
	}
	mkfSel();
	flashList();
}
/**
 * ����ѡ��
 */
function freverseSel() {
	for(var i=0; i<intMmCnt; i++) {
		if(objMmInfo[i].selMm =="t") {
			objMmInfo[i].selMm="f";
			objMmInfo[i].actMm="f";
		} else {
			objMmInfo[i].selMm="t";
			objMmInfo[i].actMm="t";
		}
	}
	mkfSel();
	flashList();
}
function chkItemfSel(idx) {
	if(objMmInfo[idx].selMm =="t") {
		objMmInfo[idx].selMm="f";
		objMmInfo[idx].actMm="f";
	} else {
		objMmInfo[idx].selMm="t";
		objMmInfo[idx].actMm="t";
	}
	mkfSel();
}
/**
 * ѡ�񲥷�ģʽ�������˳�򲥷�
 */
function chgfPMode(){
	if(blnRndPlay) {
		blnRndPlay=false;
		imgChange('pmode',0);
		frameCount.innerHTML="˳�򲥷�";
	} else {
		blnRndPlay=true;
		imgChange('pmode',1);
		frameCount.innerHTML="�������";
	}
}
/**
 * ȫ������Flash
 */
function fullplay() {
	Pause();
	var playwindow = window.open("", "newwin", "height=400, width=500,toolbar=no ,scrollbars=no,menubar=no"); 
	playwindow.document.write("<html><head><title>"+document.title+"</title></head><body onunload=\"window.opener.Pause();\">");
	playwindow.document.write("<object id=\"fullplay\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0\" width=\"100%\" height=\"100%\"><param name=\"movie\" value=\""+ osrc +"\" /><param name=\"quality\" value=\"high\" /><embed src=\""+ osrc +"\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"100%\" height=\"100%\"></embed></object>");
	playwindow.document.write("</body></html>");
	playwindow.document.close();
	imgtog('screen',1);
}