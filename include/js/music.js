/**
 * �趨������������ҳ��ʱ���Ƿ��Զ�����ý���ļ�
 * true = �Զ�����
 * false = ��Ҫ�Զ����ţ��ȴ�ʹ��������������
 */
var blnAutoStart = true;
/**
 * �趨��Ŀ��Ԥ�貥��˳���Ƿ����(�����/Random Playing)����ý��
 * true = �������
 * false = ѭ�򲥷�
 */
var blnRndPlay = false; 
var intVolume = 80;	//Ĭ������
var blnAbs1stTrack = false;
var blnStatusBar = false; 	//�Ƿ�����������ʾ����
var blnUseSmi = false;	//ʹ����Ļ����,Ԥ���ӿ�
var blnLoopTrk = true;	//Ĭ��ѭ������
var blnShowMmInfo =false;

var objMmInfo = null;
var intMmCnt = 0;
var intSelMmCnt=0;
var intActMmCnt=0;
var intCurrActIdx=0;
var strCurrActTit="nontitle";
var strCurrMediaUrl="";
var strMmInfo ="SMusic Media Information";

var blnLonelyPlay=false;
var blnEnabled= false;
var blnEOT=false;
var arrSelMm=null;
var arrActMm=null;
var intSMusicStat=0;
var tidTLab=null;
var tidErr=null;
var tidMsg=null;
var intErrCnt=0;
var blnRept=false;
var blnExtMmUsed=false;
var strExtTitle="nontitle"

var intSupremeTrkIdx=0;
var blnAutoProc = true;
var blnElapse=true; 
var intDelay =500;
var disp2 = document.getElementById('disp2');
var musicobj;
var volSliderObj;
var dTitle;

for(var i=0; i<urlarr.length; i++) {
	mkList(urlarr[i][0], urlarr[i][1]);	//���������б�
}
/**
 * ��ʼ��Windows Media Player ���ӿ⽨�������趨
 */
function wmpInit() {
	musicobj = document.getElementById('SMusic');
	var wmpEnabled=musicobj.enabled;
	if(wmpEnabled) {
		intSMusicStat=0;
	} else {
		intSMusicStat=3;	//��ʼ��Media Player OLE ����
		blnEnabled=false;
	}

	var wmps = musicobj.settings;
	var wmpc = musicobj.ClosedCaption;
	

	wmps.autoStart=true;
	wmps.balance=0;
	wmps.enableErrorDialogs=false;
	wmps.invokeURLs = false;
	wmps.mute=false;
	wmps.playCount=1;
	wmps.rate=1;
	wmps.volume = intVolume;

	if(blnUseSmi) {
		wmpc.captioningID="capText";
		document.getElementById("capText").style.display="";
	}
}

/**
 * ���� Multi-object-contents ��������
 */
function mkMmPath(u, t, f,s){
	this.mmUrl = u;
	var mmChkStr = u;
	mmChkStr = mmChkStr.toLowerCase();
	mmChkStr = mmChkStr.substring(mmChkStr.length - 4 , mmChkStr.length);
	switch(mmChkStr){
		case ".asx":
			this.mmeta="t";
			break;
		case ".wax":
			this.mmeta="t";
			break;
		case ".wvx":
			this.mmeta="t";
			break;
		case ".m3u":
			this.mmeta="t";
			break;
		default:
			this.mmeta="f";
 	}

	this.mmTit =t;
	this.mmDur =0;
	this.selMm=f;
	this.actMm=f;
	if(blnUseSmi) { this.mmSmi=s; }
}

/**
 * �� Multi-object-contents���鸽ֵ
 */
function mkList(u,t,s,f){
	var cu=u;
	var ct=t;
	var cs=s;
	var cf=f;
	var idx=0;
	if(objMmInfo == null) {
		objMmInfo=new Array();
		idx=0;
	} else {
		idx=objMmInfo.length;
	}
	if(u=="" || u==null) { cu=""; }
	if(t=="" || t==null) { ct="nontitle"; }
	if(f=="f" || f=="F") {
		cf="f";
	} else{
		cf="t";
		intSelMmCnt=intSelMmCnt+1;
	}
	blnUseSmi ? objMmInfo[idx]=new mkMmPath(cu,ct,cf,cs):objMmInfo[idx]=new mkMmPath(cu,ct,cf);
	intActMmCnt=intSelMmCnt;
	intMmCnt=objMmInfo.length;
}

/**
 * ������ѡȡ������Ŀ(Selected Media)������
 */
function mkSel(){
//	arrSelMm=null;
	intSelMmCnt=0;
	var selidx = 0;

	var wmpEnabled=musicobj.enabled;
	if(!wmpEnabled){intSMusicStat=3; blnEnabled=false;return;} //Media Play OLE ����
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

/**
 * ������ѡ��Ĳ����б�
 */
function mkAct(){
	arrActMm=null;
	intActMmCnt=0;
	var selidx=0;
	var actidx=0;

	if(blnEnabled){
		arrActMm=new Array();
		for(var i=0; i<intSelMmCnt;i++){
			selidx=arrSelMm[i];
			if(objMmInfo[selidx].actMm=="t"){arrActMm[actidx]=selidx; actidx=actidx+1;}
		}
		intActMmCnt=arrActMm.length;
	} else { return; }
	if(intActMmCnt<=0) {
		blnEOT=true;
		arrActMm=null;
	} else { blnEOT=false; }
}

/**
 * ȫ��ѡȡ���еĲ����嵥��Ŀ
 */
function chkAllSel() {
	for(var i=0; i<intMmCnt; i++){
		objMmInfo[i].selMm="t";
		objMmInfo[i].actMm="t";
	}
	mkSel();
	dspList();
}

/**
 * ȡ�����е�ý��ѡ��
 */
function chkAllDesel() {
	for(var i=0; i<intMmCnt; i++){
		objMmInfo[i].selMm="f";
		objMmInfo[i].actMm="f";
	}
	mkSel();
	dspList();
}
/**
 * ����ѡ��
 */
function reverseSel() {
	for(var i=0; i<intMmCnt; i++) {
		if(objMmInfo[i].selMm =="t") {
			objMmInfo[i].selMm="f";
			objMmInfo[i].actMm="f";
		} else {
			objMmInfo[i].selMm="t";
			objMmInfo[i].actMm="t";
		}
	}
	mkSel();
	dspList();
}
/**
 * ����ѡȡ��ѡȡ�����嵥��Ŀ������ѡ��
 */
function chkItemSel(idx) {
	if(objMmInfo[idx].selMm =="t") {
		objMmInfo[idx].selMm="f";
		objMmInfo[idx].actMm="f";
	} else {
		objMmInfo[idx].selMm="t";
		objMmInfo[idx].actMm="t";
	}
	mkSel();
}

/**
 * ��ĳ�������ò�����Ŀ(Activated Media)����
 */
function chkItemAct(idx){
	objMmInfo[idx].actMm="f";
	mkAct();
}

/**
 * ����δѡ�񵽲����б��ý��
 */
function mkSelAct(){
	var idx=0;
	for(var i=0; i<intSelMmCnt; i++){
		idx=arrSelMm[i];
		objMmInfo[idx].actMm="t";
 	}
	mkAct();
}

/**
 * ����ѡȡ������Ŀ(Selected Media)���뵽�����ò�����Ŀ(Activated Media)
 */
function inLink(trk){
	var wmps= musicobj.playState;
	if(wmps==2 || wmps==3){musicobj.controls.stop();}
	blnExtMmUsed=false;

	if(isNaN(parseInt(trk))){
		etcMsg(6,trk);
		return;
	} else {
  		var innerIdx = parseInt(trk) - 1;
  		if(innerIdx<0 || innerIdx>=intMmCnt){
			etcMsg(6,trk);	return;
  		} else { 
			blnLonelyPlay=true; 
			intErrCnt=0;
			selMmPlay(innerIdx);
		}
	}
}

/**
 * ��Media Player��ý���ַ
 * @param string url: ���ֵ�URL��ַ
 * @param string tit: ���ֱ���
 */
function outLink(url , tit){
	var wmps= musicobj.playState;
	if(wmps==2 || wmps==3){musicobj.controls.stop();}
	blnExtMmUsed=true;

	var wmpEnabled=musicobj.enabled;
	var wmps=musicobj.settings;
	if(!wmpEnabled) {
		intSMusicStat=3;	//OLE����(return void)
		waitMsg();
	} else {
		blnLonelyPlay=true;
		intErrCnt=0;
		strCurrMediaUrl=url;
		if(url==null || url=="") {etcMsg(5);return;} //Media URL Empty Error
		if(tit==null || tit==""){
			strExtTitle="nontitle";
		} else{ strExtTitle=tit; }
		strCurrActTit=strExtTitle;
		musicobj.URL=strCurrMediaUrl;
		if(!wmps.autoStart) { musicobj.controls.play(); }
		disp1.innerHTML= strCurrActTit;
		if(blnStatusBar){window.status=(strCurrActTit);}	//��״̬����ʾ����
	}
}
/**
 * ��ʼ��Media Player������
 */
function initMPlayer(){
	var re = new RegExp("(msie[^;^)^(]*)", "ig");
	if(navigator.userAgent.search(re) == -1) {
		alert("��ʹ��IE��������ʣ������޷�ʹ�ò��������ܣ�");
	}
	wmpInit();
	mkSel();
	blnLonelyPlay=false;
	blnExtMmUsed=false;
	blnRept ? imgChange('rept',1) : imgChange('rept',0);
	blnLoopTrk ? imgChange('tloop',1) : imgChange('tloop',0);
	blnRndPlay ? imgChange('pmode',1) : imgChange('pmode',0);
 	showTLab();
 	dTitle = document.getElementById('disp1');
 	dTitle.innerHTML="X-Space Music/Video Player";
 	if(blnStatusBar){window.status=('X-Space Music/Video Player');}
 	if(blnAutoStart){ startSMusic(); }
}

/**
 * ��ʼ����ý��
 */
function startSMusic() {
	var wmps= musicobj.playState;
	if(musicobj.settings.rate != 1.0){musicobj.settings.rate=1.0; return;}
	if(wmps==2){musicobj.controls.play(); return;}	//���״̬Ϊ��ֹ���򼤻��
	if(wmps==3){return;}	//�����ڲ���״̬����ֱ���˳�

	blnLonelyPlay=false;
	if(!blnEnabled) { waitMsg();return; }
	if(blnEOT) { mkSelAct(); }
	if(intErrCnt>0) {
		intErrCnt=0;
		tidErr=setTimeout('retryPlay(),1000');
		return;
	}
	if(isNaN(intSupremeTrkIdx) || intSupremeTrkIdx >= intMmCnt) { intSupremeTrkIdx=0; }
	if(blnRndPlay) {
		rndPlay();
	} else {
		if(objMmInfo[intSupremeTrkIdx].selMm=="t" && objMmInfo[intSupremeTrkIdx].actMm=="t") {
			intCurrActIdx=intSupremeTrkIdx;
			selMmPlay(intSupremeTrkIdx);
		} else {
			intCurrActIdx=arrActMm[0];
			selMmPlay(intCurrActIdx);
		}
	}
}

/**
 * ������ý����⣬���ý��·��Ϊ����������һ��ý�岥��
 */
function selMmPlay(idx){
	clearTimeout(tidErr);
	if(intSMusicStat==3){blnEnabled=false;waitMsg();return;} // WindowsMedia OLE ����
	blnExtMmUsed=false;
	intCurrActIdx=idx;
 	var trknum=idx+1;
 	var ctit =objMmInfo[idx].mmTit;
 	if(ctit=="nontitle") {ctit="���ڻ�ȡ��������";}
 	if(blnUseSmi){musicobj.ClosedCaption.SAMIFileName = objMmInfo[idx].mmSmi;}
 	strCurrMediaUrl=objMmInfo[idx].mmUrl;
	//���ý��·��Ϊ�գ��򲥷���һ��ý��
 	if(strCurrMediaUrl=="" || strCurrMediaUrl==null){ etcMsg(4);setTimeout('playAuto()', 3000);return; }
 	musicobj.URL = strCurrMediaUrl;
 	var wmps=musicobj.settings;
 	if(!wmps.autoStart) { musicobj.controls.play(); }
 	if(blnAbs1stTrack && intCurrActIdx==intSupremeTrkIdx) {
 		strCurrActTit="[SupremeTrack:" + trknum + "] " + ctit;
 	} else { strCurrActTit= " "+ trknum + ". " + ctit; }
	dTitle.innerHTML = strCurrActTit;
	if(blnStatusBar) { window.status=(strCurrActTit); }
	chkItemAct(intCurrActIdx); 
}

/**
 * ʹ��wmp-obj����ý��
 */
function wmpPlay() { musicobj.controls.play(); }

/**
 * ֹͣ��ȴ�����
 */
function wmpStop(){
	intErrCnt=0;
	clearTimeout(tidErr);
	clearInterval(tidTLab);
	imgChange("stopt",1);
	imgChange("pauzt",0);
	showTLab();
	mkSelAct();
	musicobj.controls.stop();
	musicobj.close();
	dTitle.innerHTML="�ȴ�����";
	if(blnStatusBar) {window.status=('�ȴ�����');return true;}
}

/**
 * ��ͣwmp-obj����ý��
 */
function wmpPause() { musicobj.controls.pause(); }

/**
 * ��ͣ�򲥷�ý��
 */
function wmpPP(){
	var wmps = musicobj.playState;
	var wmpc=musicobj.controls;
	clearInterval(tidTLab);
	clearTimeout(tidMsg);
	if (wmps == 2) {wmpc.play();}
	if (wmps == 3) {
		wmpc.pause();
 		disp2.innerHTML="��ͣ";
 		tidMsg=setTimeout('rtnTLab()',1500);
 	}
 	return;
}
/**
 * ����
 */
function fastRew() {
	clearInterval(tidTLab);
	var wmpfr=musicobj.controls.isAvailable("FastReverse");
	if(wmpfr){musicobj.controls.fastReverse();
		disp2.innerHTML="FastRWD"
	} else {
		disp2.innerHTML="noReward";
	}
}
/**
 * ǰ��
 */
function fastFwd() {
	clearInterval(tidTLab);
	var wmpff=musicobj.controls.isAvailable("FastForward");
	if(wmpff){musicobj.controls.fastForward();
		disp2.innerHTML="FastFWD"
	} else {
		disp2.innerHTML="noForward"
	}
}

function endFwd(){
	if(musicobj.settings.rate >1.0) {
  		musicobj.settings.rate=1.0;
	} else {
		tidTLab=setInterval('showTLab()',1000);
	}
}

function endRew(){
	if(musicobj.settings.rate <1.0) {
		musicobj.settings.rate=1.0;
	} else {
		tidTLab=setInterval('showTLab()',1000);
	}
}

/**
 * �������(Random Play)ģʽ
 */
function rndPlay(){
	if(!blnEnabled) {waitMsg();return;}
	intErrCnt=0;
	if(blnAbs1stTrack){
		if(objMmInfo[intSupremeTrkIdx].selMm=="t" && objMmInfo[intSupremeTrkIdx].actMm=="t") {
			intCurrActIdx=intSupremeTrkIdx;
			selMmPlay(intSupremeTrkIdx);
		} else {
			var idx=Math.floor(Math.random() * intActMmCnt);
		 	intCurrActIdx= arrActMm[idx];
			selMmPlay(intCurrActIdx);
		}
 	} else {
		var idx=Math.floor(Math.random() * intActMmCnt);
	 	intCurrActIdx= arrActMm[idx];
		selMmPlay(intCurrActIdx);
	} 
}

/**
 * �������ò�����Ŀ���С��Զ��������š��Ĵ���
 * ���Ǹ������� blnAutoProc ���趨ֵ�������Ķ�����
 */
function playAuto(){
	var wmps=musicobj.playState;
	if(wmps>1 && wmps<10){return;}

	if(!blnAutoProc){wmpStop();return;}
	if(blnLonelyPlay){wmpStop(); return;} 
	if(!blnEnabled){wmpStop();return;}
	if(blnEOT) {
		if(blnLoopTrk) {
			startSMusic();
		} else { wmpStop(); }
 	} else {
		if(blnRndPlay) {
			rndPlay();
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
			selMmPlay(intCurrActIdx); 
		}

	}
}

/**
 * ����ʹ�����ڲ����嵥������ѡ�ĵ�һ��Ŀ
 */
function selPlPlay(idx){
	var wmps= musicobj.playState;
	if(wmps==2 || wmps==3) { musicobj.controls.stop(); }
 	blnLonelyPlay=false; 
	intErrCnt=0;
	selMmPlay(idx);
}
/**
 * ������һ��
 */
function playPrev(){
	var wmps= musicobj.playState;
	if(wmps==2 || wmps==3) { musicobj.controls.stop(); }
	blnLonelyPlay=false;
	if(!blnEnabled) { waitMsg();return; }
	if(blnEOT) { mkSelAct(); }
	intErrCnt=0;
	if(blnRndPlay) {
		rndPlay();
	} else {
		var idx=intCurrActIdx;
 		var blnFind=false;
		for(var i=0;i<intSelMmCnt;i++) {
			if(intCurrActIdx==arrSelMm[i]) {
				idx=i-1;
				blnFind=true;
			}
		}
		if(!blnFind) { startSMusic();return; }
		if(idx<0) {
			idx=intSelMmCnt-1;
			intCurrActIdx=arrSelMm[idx];
		} else { intCurrActIdx=arrSelMm[idx]; }
		selMmPlay(intCurrActIdx);
	}
}
/**
 * ������һ��ý��
 */
function playNext(){
	var wmps= musicobj.playState;
	if(wmps==2 || wmps==3) { musicobj.controls.stop(); }
	blnLonelyPlay=false;
 	if(!blnEnabled){waitMsg();return;}
 	if(blnEOT){mkSelAct();}
	intErrCnt=0;
	if(blnRndPlay) {
		rndPlay();
	} else{
		var idx=intCurrActIdx;
 		var blnFind=false;
		for(var i=0;i<intSelMmCnt;i++) {
			if(intCurrActIdx==arrSelMm[i]) {
				idx=i+1;
				blnFind=true;
			}
		}
		if(!blnFind) { startSMusic();return; }
		if(idx>=intSelMmCnt) {
			idx=0;
			intCurrActIdx=arrSelMm[idx];
		} else {
			intCurrActIdx=arrSelMm[idx];
		}
		selMmPlay(intCurrActIdx);
	}
}

/**
 * ���Ե�ǰ����ý��
 */
function retryPlay(){
	var wmps=musicobj.settings;
	musicobj.URL=strCurrMediaUrl;
	if(!wmps.autoStart) { musicobj.controls.play(); }
}

/**
 * �ظ����뵱ǰý��
 */
function chkRept(){
	var wmps=musicobj.playState;
	if(wmps == 3) { clearInterval(tidTLab); }
	if(blnRept) {
		musicobj.settings.playCount=1;
		blnRept=false;
		imgChange('rept',0);
		disp2.innerHTML="��������";
	} else {
		musicobj.settings.playCount=65535;
		blnRept=true;
		imgChange('rept',1);
		disp2.innerHTML="�ظ�����";
	}
	tidMsg= setTimeout('rtnTLab()',1000);
}
/**
 * ѡ�񲥷�ģʽ�������˳�򲥷�
 */
function chgPMode(){
	var wmps=musicobj.playState;
	if(wmps == 3) { clearInterval(tidTLab); }
	if(blnRndPlay) {
		musicobj.settings.setMode("shuffle", false);
		blnRndPlay=false;
		imgChange('pmode',0);
		disp2.innerHTML="˳�򲥷�";
	} else {
		musicobj.settings.setMode("shuffle", true); 
		blnRndPlay=true;
		imgChange('pmode',1);
		disp2.innerHTML="�������";
	}
	tidMsg=setTimeout('rtnTLab()',1000); 
}
/**
 * ѭ������
 */
function chgTrkLoop(){
	var wmps=musicobj.playState;
	if(wmps == 3) { clearInterval(tidTLab); }
	if(blnLoopTrk) {
		blnLoopTrk=false;
		imgChange('tloop',0);
		disp2.innerHTML="��ѭ������";
	} else {
		blnLoopTrk=true;
		imgChange('tloop',1);
		disp2.innerHTML="ѭ������";
	}
	tidMsg=setTimeout('rtnTLab()',1000); 
}
/**
 * 0(Undefined) 8(MediaChanging) 9(MediaLocating) 10(MediaConnecting) 11(MediaLoading)
 * 12(MediaOpening) 13(MediaOpen) 20(MediaWaiting) 21(OpeningUnknownURL)
 */
function evtOSChg(f) {
	if(f==8 && blnUseSmi){document.getElementById('capText').innerHTML="";}
	if(f==9){disp2.innerHTML="(Access)"; imgChange("playt",6); if(blnStatusBar){window.status=('(Wait...Media Locating)');}} //display 'buffering' image
	if(f==10){disp2.innerHTML="(Connect)"; imgChange("playt",6); if(blnStatusBar){window.status=('(Wait...Media Connecting)');}} //display 'buffering' image
	if(f==11){disp2.innerHTML="(Loading)"; imgChange("playt",6); if(blnStatusBar){window.status=('(Wait...Media Loading)');}} //display 'buffering' image
	if(f==12){disp2.innerHTML="(Opening)"; imgChange("playt",6); if(blnStatusBar){window.status=('(Wait...Media Opening)');}} //display 'buffering' image
	if(f==20){disp2.innerHTML="(Waiting)"; imgChange("playt",6); if(blnStatusBar){window.status=('(Wait...Media Waiting)');}} //display 'buffering' image
	if(f==21){disp2.innerHTML="(Opening)"; imgChange("playt",6); if(blnStatusBar){window.status=('(Wait...Unknown Media URL)');}} //display 'buffering' image
	if(f==13) {
		var strTitle = musicobj.currentMedia.getItemInfo("title");
		if(strTitle.length <=0) { strTitle = "Unknown" }
		var strAuthor = musicobj.currentMedia.getItemInfo("Author");
		if(strAuthor.length <=0) { strAuthor = "Unknown" }
		var strCopy = musicobj.currentMedia.getItemInfo("Copyright");
		if(strCopy.length <=0) { strCopy = "Unknown" }
		var strType = musicobj.currentMedia.getItemInfo("MediaType");
		var strBitrate = musicobj.currentMedia.getItemInfo("Bitrate");
		var strBandwidth =  musicobj.network.bandwidth;
		var strDur=musicobj.currentMedia.durationString;
		var strUrl =musicobj.currentMedia.sourceURL;


		strMmInfo= "Media Title : " + strTitle + "\n\n"
		strMmInfo= strMmInfo + "Media Author : " + strAuthor + "\n\n"
		strMmInfo= strMmInfo + "Media URL : " +strUrl + "\n\n"
		strMmInfo= strMmInfo + "Media Copyright : " + strCopy +"\n\n" 
		strMmInfo= strMmInfo + "Media Type : " +strType +"\n\n"
		strMmInfo= strMmInfo + "Media Duration : " +strDur +"\n\n"
		strMmInfo= strMmInfo + "Media Bitrate : " + parseInt(strBitrate/1000) + " KBit/sec \n\n"
		strMmInfo= strMmInfo + "Media Bandwidth : " + parseInt(strBandwidth/1000) + " KHz \n\n"
		strMmInfo= strMmInfo + " (C)Copyright SupeSite/X-Space  \n";
		if(blnShowMmInfo){alert(strMmInfo);}

		if(blnExtMmUsed){
			if(strExtTitle=="nontitle"){strExtTitle="LoadTitle";}
			strCurrActTit=strExtTitle + "(Info: " + strAuthor + " - " + strTitle + ")";
			dTitle.innerHTML = strCurrActTit;
			if(blnStatusBar){window.status=(strCurrActTit);}
			return;
		}

		var trknum=intCurrActIdx+1;
		var ctit = objMmInfo[intCurrActIdx].mmTit;
	
		if(ctit=="nontitle" && objMmInfo[intCurrActIdx].mmeta=="f"){ 
			objMmInfo[intCurrActIdx].mmTit = "(Title) " + strAuthor + " - " + strTitle;
			ctit="(Info) " + strAuthor + " - " + strTitle;
			if(blnAbs1stTrack && intCurrActIdx==intSupremeTrkIdx){strCurrActTit= "[SupremeTrack:" + trknum + "] " + ctit;}
			else{strCurrActTit= " "+ trknum + ". " + ctit; }
		}

		if(objMmInfo[intCurrActIdx].mmeta=="t") {
			if(ctit=="nontitle") {
				if(blnAbs1stTrack && intCurrActIdx==intSupremeTrkIdx) {
					strCurrActTit="[SupremeTrack:"+ trknum +"] " + " ASXmode (Title:" + strAuthor + "- " + strTitle + ")" ;
				} else {
					strCurrActTit= " "+trknum + ". " + " ASXmode (Title:" + strAuthor +"- "+strTitle+")";
				}
			} else {
				if(blnAbs1stTrack && intCurrActIdx==intSupremeTrkIdx) {
					strCurrActTit="[SupremeTrack:"+trknum+"] " + ctit + " (Title:" + strAuthor +"- "+strTitle+")";
				} else {
					strCurrActTit= " "+trknum + ". " + ctit + " (Title:" + strAuthor +"- "+strTitle+")";
				}
			}
		}
		dTitle.innerHTML = strCurrActTit;

	}
}
/**
 * 0(Undefined) 1(Stopped) 2 (Paused) 3(Playing) 4(ScanFowrd) 5(ScanReverse)
 * 6(Buffering) 7(Waitng) 8(MediaEnded) 9(Transitioning) 10(Ready)
 */
function evtPSChg(f){
	switch(f){
		case 1:
			evtStop();
			break;
		case 2:
			evtPause();
			break;
		case 3:
			evtPlay();
			break;
		case 7:
			evtWait();
			break;
		case 8:
			setTimeout('playAuto()', intDelay);
			break;
	}
}

function evtWmpBuff(f) {
//	disp1 = document.getElementById('disp1');
	if(f){ disp2.innerHTML = "Buffering";
		var msg="(Buffering) " + strCurrActTit;
		dTitle.innerHTML = msg;
		imgChange("playt",6);
		if(blnStatusBar){window.status=(msg);}
	} else { 
		dTitle.innerHTML = strCurrActTit;
		showTLab();
		imgtog('playt',3);
	}
}


function evtWmpError() {
	intErrCnt=intErrCnt+1;
	musicobj.Error.clearErrorQueue();
	imgChange("pauzt",0);
	imgChange("playt",0);
	if(intErrCnt<=3) {
		strCurrMediaUrl=musicobj.URL;
		disp2.innerHTML="����("+intErrCnt+")";
		var msg="(����:" + intErrCnt +") " +strCurrActTit;
		dTitle.innerHTML="<����> " +strCurrActTit;
		if(blnStatusBar){window.status=(msg);}
		tidErr=setTimeout('retryPlay()',1000);
	} else{	
		clearTimeout(tidErr);
		intErrCnt=0;showTLab();
		var msg="���ڶ�ȡ����ý���ļ�����";
		dTitle.innerHTML=msg;
		if(blnStatusBar){window.status=(msg);}	
		if(!blnLonelyPlay && blnAutoProc) { setTimeout('playAuto()',1000); }
	}
}

function evtWait() {
	disp2.innerHTML="(�ȴ�)";
	if(blnStatusBar){window.status=('(Wait...Media Waiting)');}
	imgChange("playt",6); //display 'buffering' image
}

function evtStop(){
	clearTimeout(tidErr);
	clearInterval(tidTLab);
	showTLab();
	intErrCnt=0;
	imgtog('vmute',3);
	imgChange("pauzt",0);
	imgChange("playt",0);
	dTitle.innerHTML="���ŵȴ��С���";
	if(blnStatusBar){window.status=('���ŵȴ��С���');return true;}
}

function evtPause(){
	imgChange("pauzt",1)
	imgChange("playt",0);
	imgChange("stopt",0);
	clearInterval(tidTLab);
	showTLab();
}

function evtPlay(){
	imgChange("pauzt",0)
	imgChange("playt",1);
	imgChange("stopt",0);
	imgtog('vmute',3); //recover abnormal 'mute' image
	tidTLab=setInterval('showTLab()',1000);
}

/**
 * ��ʾʱ�䳤��Displaying Timer label(Elapse,Lapse)
 */
function showTLab(){
	var ps=musicobj.playState;
	disp2 = document.getElementById('disp2');
	if(ps==2 || ps==3){
		var cp=musicobj.controls.currentPosition
		var cps=musicobj.controls.currentPositionString
		var dur=musicobj.currentMedia.duration;
		var durs=musicobj.currentMedia.durationString;
		if(isNaN(dur) || dur==0){durs="(AIR)";}
		if(blnElapse){disp2.innerHTML= cps+" | "+durs;
			var msg=strCurrActTit + " ("+cps+" | "+durs+ ")";
			if(ps==2){msg="(Pause) "+ msg;}
			if(blnStatusBar){window.status=(msg);return true;}
		} else {
			var lapse
			if(isNaN(dur) || dur==0) {
				strLapse="Live";
			} else {
				lapse=dur-cp;
				var strLapse=wmpTime(lapse);
			}
			disp2.innerHTML= strLapse + " | "+durs;
			var msg= strCurrActTit + " (" + strLapse + " | "+durs + ")";
			if(ps==2) { msg="(��ͣ) "+ msg; }
			if(blnStatusBar) { window.status=(msg);return true; }
		}

	} else {disp2.innerHTML="00:00 | 00:00"; }
}

/**
 * ʱ����ʾģʽ
 */
function chgTimeFmt(){
	var wmps=musicobj.playState;
	if(wmps == 3) { clearInterval(tidTLab); }
	if(blnElapse) {
		blnElapse=false;
		disp2.innerHTML="����ʱ";
	} else {
		blnElapse=true;
		disp2.innerHTML="˳��ʱ";
	}
 	tidMsg=setTimeout('rtnTLab()',1000); 
}

/**
 * ��ʾʱ��ָʾ��
 */
function rtnTLab(){
	clearTimeout(tidMsg);
	var wmps=musicobj.playState;
	wmps == 3 ? tidTLab=setInterval('showTLab()',1000) : showTLab();
}
/**
 * ����ʱ�䳤��
 */
function wmpTime(dur){
	if(isNaN(dur) || dur==0){return "Live";}
	var hh, min, sec, timeLabel
	hh = Math.floor(dur/3600);
	min = Math.floor(dur / 60)%60;
	sec = Math.floor(dur % 60);
	if (isNaN(min)){ return "00:00"; }
	if (isNaN(hh) || hh==0) {
		timeLabel="";
	} else {
 		if(hh >9) {
			timeLabel = hh.toString()+":";
		} else { timeLabel="0"+hh.toString() +":"; }
	}
	if ( min > 9 ) {
		timeLabel = timeLabel + min.toString() + ":";
	} else {
		timeLabel = timeLabel + "0" +min.toString() + ":";
	}
	if ( sec > 9 ) {
		timeLabel = timeLabel + sec.toString();
	} else {
		timeLabel = timeLabel + "0" + sec.toString();
	}
	return timeLabel;
}
/**
 * ����
 */
function wmpMute() {
	var wmps=musicobj.playState;
	if(wmps == 3) { clearInterval(tidTLab); }
	var ps = musicobj.settings;
	if(!ps.mute) {
		ps.mute = true;
		disp2.innerHTML="����";
		imgChange("vmute", 1);
	} else {
		ps.mute = false;
		disp2.innerHTML="ȡ������";
		imgChange("vmute", 0)
	}
	tidMsg=setTimeout('rtnTLab()',1000);
}
/**
 * ��ʾ����
 */
function prnVol() { disp2.innerHTML= "����. " + musicobj.settings.Volume + "%"; }
/**
 * ��ʾָ������Ϣ
 */
function waitMsg(){
 var outMsg="";
 if(blnUseSmi){document.getElementById('capText').innerHTML="";}
 switch(intSMusicStat){
	case 1 :
		outMsg="�Բ���û���ҵ�����ý���б�";
		dTitle.innerHTML=outMsg;
		if(blnStatusBar){window.status=(outMsg);return true;}
		break;
	case 2 :
		outMsg="��ѡ������ŵ�ý���ļ�";
		dTitle.innerHTML=outMsg;
		if(blnStatusBar){window.status=(outMsg); return true;}
		break;
	case 3 :
		outMsg="����: ��������Windows Media Player�����⣬��ȷ��Media Player�Ƿ����";
		dTitle.innerHTML=outMsg;
		if(blnStatusBar){window.status=(outMsg);return true;}
		break;

	default :
		outMsg="SupeSite/X-Space ý�岥����";
		dTitle.innerHTML=outMsg;
		if(blnStatusBar){window.status=(outMsg);return true;}
 }
}

function etcMsg(f, e1, e2){
	var outMsg="";
	if(blnUseSmi){document.getElementById('capText').innerHTML="";}
	switch(f){
		case 4 :
			outMsg="����: ý�� "+ (intCurrActIdx+1) + ". ·��Ϊ��";
			dTitle.innerHTML=outMsg;
			if(blnStatusBar){window.status=(outMsg);return true;}
			break;
	
		case 5 :
			outMsg="����: ý��·��Ϊ�գ�������·��";
			dTitle.innerHTML=outMsg;
			if(blnStatusBar){window.status=(outMsg);return true;}
			break;
		case 6 :
			outMsg="�Բ����Ҳ���ý�� No. " + e1 ;
			dTitle.innerHTML=outMsg;
			if(blnStatusBar){window.status=(outMsg);return true;}
			break;
		default :
			outMsg="SupeSite/X-Space ý�岥����";
			dTitle.innerHTML=outMsg;
			if(blnStatusBar){window.status=(outMsg);return true;}
	}
}

function fullScreen(){
	var wmps=musicobj.playState;
	if(wmps==2 || wmps==3 ){ musicobj.fullscreen=true;}
}

/**
 * �����ų��������ʱ������ playState ��״ֵ̬
 * 0(Undefined) 1(Stopped) 2 (Paused) 3(Playing) 4(ScanFowrd) 5(ScanReverse)
 * 6(Buffering) 7(Waitng) 8(MediaEnded) 9(Transitioning) 10(Ready)
 */
function chkWmpState(){
	return musicobj.playState;
}
/**
 * �����ų�����ý�嵵��׼������ʱ������ openState ��״ֵ̬
 * @return 0(Undefined) 8(MediaChanging) 9(MediaLocating) 10(MediaConnecting) 11(MediaLoading)12(MediaOpening) 13(MediaOpen) 20(MediaWaiting) 21(OpeningUnknownURL)
 */
function chkWmpOState(){
	return musicobj.openState;
}
/**
 * ���ʹ���ߵ�����״̬
 * @return true(��������������) false(û��������������)
 */
function chkOnline() {
	return musicobj.isOnline;
}

/**
 * �������ڹ���
 */
document.onmouseup=function() { blnDragging=false; }
var sliderPosLeft;
var volSliderLength=48;
var blnDragging=false;
var mPosX;
var evtSrcObj;
var eobj;

/**
 * ��ʼ����������
 */
function initVol() {
	var ps=intVolume;
	if(isNaN(ps) || ps<0) { ps=0; }
	else if(ps>=100){ps=100;}
	volSliderObj = document.getElementById('volSlider');
	sliderPosLeft = parseInt(volSliderObj.style.left);
	volSliderObj.style.left = (sliderPosLeft + Math.floor(volSliderLength * ps/100)) + "px";;
}

function volTracking() {
	if (blnDragging) {	// && eobj.button==1
		musicobj.settings.Mute=false;
		imgChange("vmute", 0)
		var sliderLength= volSliderLength;
		var minLimit= 14;
		var maxLimit= 75;
		var mov =  sliderPosLeft + eobj.clientX - mPosX;
		if(mov <= minLimit){
			volSliderObj.style.left=minLimit + 'px';
			musicobj.settings.volume=0;
 			prnVol();
		}
		if(mov > maxLimit){
			volSliderObj.style.left=maxLimit + 'px';;
			musicobj.settings.volume=100;
 			prnVol();
		}
		if ((mov <= maxLimit) &&  (mov > minLimit )){
			volSliderObj.style.left = (sliderPosLeft + eobj.clientX - mPosX) + 'px';
			musicobj.settings.volume=Math.round(((mov-minLimit-2)/(sliderLength))*100);
 			prnVol();
		}

	}
	return false;
}
function setVol() {
	blnDragging=false;
}
function readyDrag(e) {
	if(document.layers){return false;}
	if(typeof e.srcElement != 'undefined') {
		evtSrcObj = e.srcElement.id;
	} else {
		evtSrcObj = e.target.id;
	}
	switch(evtSrcObj){
		case "volSlider" :
			mPosX=e.clientX;
			sliderPosLeft = parseInt(volSliderObj.style.left);
			blnDragging=true;
			eobj = e;
			document.onmouseup = setVol;
			volSliderObj.onmousemove = volTracking;
			break;
		case "procSlider" :
			moveProc();
			break;
		default :
			return false;
	}
}
/**
 * ý���б�
 */
function playSel(){wmpStop();startSMusic();}

function dspList(){
	musicList = document.getElementById('xspace-mmList');
	if(intMmCnt >0 ){
		var list_num=0;
		document.getElementById('xspace-mmList').innerHTML='';
		for (var i=0; i < intMmCnt; i++) {
			list_num = i + 1;
			if(objMmInfo[i].selMm=="t") {
				elm=' <input type=checkbox  style="cursor:pointer;" onClick=chkItemSel('+ i +'); checked>' ;
			} else {
				elm = ' <input type=checkbox style="cursor:pointer;" onClick=chkItemSel('+ i +');>' ;
			}
			elm = elm + '&nbsp;' + list_num + '. ' 
			elm = elm + '<a href=javascript:;'
			elm = elm + ' onclick=\"selPlPlay(' + i + ');this.blur();\">'
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
