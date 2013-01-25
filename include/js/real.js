

var realobj;
var disp2;
var dTitle;
var volSliderObj;
var sliderPosLeft;
/**
 * �趨��Ŀ��Ԥ�貥��˳���Ƿ����(�����/Random Playing)����ý��
 * true = �������
 * false = ѭ�򲥷�
 */
var blnRndPlay = false;
var mute = false;
var blnRept=false;
var blnLoopTrk = true;	//Ĭ��ѭ������
var blnRept = false;
var blnElapse = true;
var blnAutoStart = true;
var blnAbs1stTrack = false;
var blnAutoProc = true;
var blnLonelyPlay=false;
var blnEOT = false;
var blnEnabled = false;
var blnUseSmi = false;	//ʹ����Ļ����,Ԥ���ӿ�
var intDelay = 1000;
var volSliderLength = 48;
var intSMusicStat = 0;
var intVolume = 80;	//Ĭ������
var num = 0;
var clicknum = 0;
var intMmCnt = 0;
var intSupremeTrkIdx = 0;
var intCurrActIdx = 0;
var intSelMmCnt = 0;
var tidErr = null;
var tidTLab = null;
var tidMsg = null;
var objMmInfo = null;
var arrSelMm=null;
var arrActMm=null;

for(var i=0; i<urlarr.length; i++) {
	mkList(urlarr[i][0], urlarr[i][1]);	//���������б�
}

/**
 * ����
 */
function play() {
	tidMsg = setTimeout('rtnTLab()', 800);
	realobj.DoPlay();
}

/**
 * ��ͣ
 */
function playPause() {
	var realps = playState();
	clearInterval(tidTLab);
	clearTimeout(tidMsg);
	if (realps == 4) {play();}
	if (realps == 3) {
		realobj.DoPause();
 		disp2.innerHTML = "��ͣ";
 		tidMsg=setTimeout('rtnTLab()', 1500);
 	}
 	return;
}

/**
 * ֹͣ
 */
function playStop() {
	realobj.DoStop();
}

function setMute() {
	mute = !mute;
	realobj.SetMute(mute);
}
function setPosition(num) {
	realobj.SetPosition(num);
}

function getPosition() {
	return realobj.GetPosition();
}

function getLength() {
	return realobj.GetLength();
}

function setVolume(num) {
	realobj.SetVolume(num);
}

function showTime(num) {
	var minute = Math.floor(num/60000);
	var second = Math.floor((num - minute*60000)/1000);
	if(minute < 10) {
		minute = "0" + minute;
	}
	if(second < 10) {
		second = "0" + second;
	}
	return (minute + ":" +second); 
}

/**
 * ������״̬
 */
function playState() {
	return realobj.GetPlayState();
}

function transformTime(num) {
	var minute = Math.floor(num / 60000);
	var second = Math.floor((num - minute*60000) / 1000);
	if( minute < 10 ) {
		minute = "0" + minute;
	}
	if( second < 10 ) {
		second = "0" + second;
	}
	return (minute + ":" +second); 
}

/**
 * ��ʾʱ�䳤��Displaying Timer label(Elapse,Lapse)
 */
function showTLab() {
	var ps = playState();
	disp2 = getbyid('disp2');
	if(ps == 3 || ps == 4){
		var cp = getPosition();
		var cps = transformTime(getPosition());
		var dur = getLength();
		var durs = transformTime(getLength());
		
		if(isNaN(dur) || dur == 0){durs = "(AIR)";}
		if(blnElapse) {
			disp2.innerHTML = cps+" | "+durs;
		} else {
			var lapse;
			if(isNaN(dur) || dur == 0) {
				strLapse = "Live";
			} else {
				lapse = dur-cp;
				var strLapse = transformTime(lapse);
			}
			disp2.innerHTML = strLapse + " | "+durs;
		}
	} else {disp2.innerHTML = "00:00 | 00:00"; }
}

/**
 * �ظ����뵱ǰý��
 */
function chkRept() {
	var reps = playState();
	if(reps == 3) {clearInterval(tidTLab);}
	if(blnRept) {
		realobj.SetLoop(false);
		blnRept = false;
		imgChange('rept', 0);
		disp2.innerHTML="��������";
	} else {
		realobj.SetLoop(true);
		blnRept=true;
		imgChange('rept',1);
		disp2.innerHTML="�ظ�����";
	}
	tidMsg= setTimeout('rtnTLab()', 1000);
}

/**
 * ʱ����ʾģʽ
 */
function chgTimeFmt() {
	var rps=playState();
	if(rps == 3) {clearInterval(tidTLab);}
	if(blnElapse) {
		blnElapse = false;
		disp2.innerHTML = "����ʱ";
	} else {
		blnElapse = true;
		disp2.innerHTML = "˳��ʱ";
	}
 	tidMsg=setTimeout('rtnTLab()', 1000);
}

function fastPlayer() {
	var rstate  = playState();
	if(rstate == 3) {
		var direction = num*19000;
		var judger = (getPosition() + direction);
		if(judger >= 0 && judger <= getLength()) {
			setPosition(judger);
		}
	}
}
function fastRew() {
	if(clicknum < 0) clicknum = 0;
	clicknum++;
	num = clicknum;
	fastPlayer();
}

function forward() {
	if(clicknum > 0) clicknum = 0;
	clicknum--;
	num = clicknum;
	fastPlayer();
}
function startSMusic() {
	evtPlay();
	realobj = getbyid('SMusic');
	if(blnEOT) {mkSelAct();}
	var rps = playState();
	if(rps == 1||rps == 2) {
		return;
	}
	if(rps == 4 || rps == 3) {
		play();
		return;
	}
	if(isNaN(intSupremeTrkIdx) || intSupremeTrkIdx >= intMmCnt) { intSupremeTrkIdx = 0; }
	if(blnRndPlay) {
		rndPlay();
	} else {
		if(objMmInfo[intSupremeTrkIdx].selMm == "t" && objMmInfo[intSupremeTrkIdx].actMm == "t") {
			intCurrActIdx = intSupremeTrkIdx;
			selMmPlay(intSupremeTrkIdx);
		} else {
			intCurrActIdx = arrActMm[0];
			selMmPlay(intCurrActIdx);
		}
	}
	play();
}

/**
 * �������(Random Play)ģʽ
 */
function rndPlay() {
	if(blnAbs1stTrack) {
		if(objMmInfo[intSupremeTrkIdx].selMm == "t" && objMmInfo[intSupremeTrkIdx].actMm == "t") {
			intCurrActIdx = intSupremeTrkIdx;
			selMmPlay(intSupremeTrkIdx);
		} else {
			var idx = Math.floor(Math.random() * intActMmCnt);
		 	intCurrActIdx = arrActMm[idx];
			selMmPlay(intCurrActIdx);
		}
 	} else {
		var idx = Math.floor(Math.random() * intActMmCnt);
	 	intCurrActIdx = arrActMm[idx];
		selMmPlay(intCurrActIdx);
	} 
}

/**
 * ������ý����⣬���ý��·��Ϊ����������һ��ý�岥��
 */
function selMmPlay(idx) {
	clearTimeout(tidErr);
	blnExtMmUsed = false;
	intCurrActIdx = idx;
 	var trknum = idx+1;
 	var ctit = objMmInfo[idx].mmTit;
 	if(ctit == "nontitle") {ctit = "���ڻ�ȡ��������";}
 	strCurrMediaUrl = objMmInfo[idx].mmUrl;
	//���ý��·��Ϊ�գ��򲥷���һ��ý��
 	if(strCurrMediaUrl == "" || strCurrMediaUrl == null){setTimeout('playAuto()', 3000);return;}
 	realobj.SetSource(strCurrMediaUrl);

 	var autoStart = realobj.GetAutoStart();
 	if(!autoStart) {play();}
 	if(blnAbs1stTrack && intCurrActIdx == intSupremeTrkIdx) {
 		strCurrActTit = "[SupremeTrack:" + trknum + "] " + ctit;
 	} else {
 		strCurrActTit = " "+ trknum + ". " + ctit;
 	}
	dTitle.innerHTML = strCurrActTit;
	chkItemAct(intCurrActIdx);
}
function evtPSChg(f) {
	switch(f){
		case 0:
			evtStop();
			setTimeout('playAuto()', intDelay);
			break;
		case 3:
			evtPlay();
			break;
		case 4:
			evtPause();
			break;
		case 5:
			evtWait();
			break;
	}
}

function evtWait() {
	disp2.innerHTML="(�ȴ�)";
	imgChange("playt", 0);
	imgChange("stopt", 0);
	imgChange("playt", 0); //display 'buffering' image
}

function evtStop() {
	clearTimeout(tidErr);
	clearInterval(tidTLab);
	showTLab();
	imgtog('vmute', 3);
	imgChange("pauzt", 0);
	imgChange("playt", 0);
	dTitle.innerHTML="���ŵȴ��С���";
}

function evtPause() {
	imgChange("pauzt", 1);
	imgChange("playt", 0);
	imgChange("stopt", 0);
	clearInterval(tidTLab);
	showTLab();
}

function evtPlay() {
	imgChange("pauzt", 0);
	imgChange("playt", 1);
	imgChange("stopt", 0);
	imgtog('vmute', 3); //recover abnormal 'mute' image
 	var trknum = intCurrActIdx+1;
 	var ctit = objMmInfo[intCurrActIdx].mmTit;
 	if(ctit == "nontitle") {ctit = "���ڻ�ȡ��������";}
	if(blnAbs1stTrack && intCurrActIdx == intSupremeTrkIdx) {
 		strCurrActTit = "[SupremeTrack:" + trknum + "] " + ctit;
 	} else {
 		strCurrActTit = " "+ trknum + ". " + ctit;
 	}
	dTitle.innerHTML = strCurrActTit;
	tidTLab=setInterval('showTLab()',1000);
}

/**
 * ��ʾʱ��ָʾ��
 */
function rtnTLab() {
	clearTimeout(tidMsg);
	playState() == 3 ? tidTLab=setInterval('showTLab()',1000) : showTLab();
}

/**
 * ֹͣ��ȴ�����
 */
function realStop() {
	clearTimeout(tidErr);
	clearInterval(tidTLab);
	imgChange("stopt",1);
	imgChange("pauzt",0);
	showTLab();
	mkSelAct();
	playStop();
	dTitle.innerHTML="�ȴ����š���";
}



function volTracking() {
	if (blnDragging) {	// && eobj.button==1
		realobj.SetMute(false);
		imgChange("vmute", 0)
		var sliderLength = volSliderLength;
		var minLimit = 14;
		var maxLimit = 75;
		var mov =  sliderPosLeft + eobj.clientX - mPosX;
		if(mov <= minLimit) {
			volSliderObj.style.left = minLimit + 'px';
			realobj.SetVolume(0);
 			prnVol();
		}
		if(mov > maxLimit) {
			volSliderObj.style.left = maxLimit + 'px';
			realobj.SetVolume(100);
 			prnVol();
		}
		if ((mov <= maxLimit) &&  (mov > minLimit )) {
			volSliderObj.style.left = (sliderPosLeft + eobj.clientX - mPosX) + 'px';
			realobj.SetVolume(Math.round(((mov-minLimit-2)/(sliderLength))*100));
 			prnVol();
		}

	}
	return false;
}

function prnVol() { disp2.innerHTML = "����. " + realobj.GetVolume() + "%"; }

/**
 * ��ʼ����������
 */
function initVol() {
	var ps = intVolume;
	if(isNaN(ps) || ps<0) {ps = 0;}
	else if(ps>=100){ps = 100;}
	volSliderObj = getbyid('volSlider');
	sliderPosLeft = parseInt(volSliderObj.style.left);
	volSliderObj.style.left = (sliderPosLeft + Math.floor(volSliderLength * ps/100)) + "px";
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
		default: break;			
	}
}

function imgmute() {
	var ps=realobj.GetMute();
	ps?imgChange("vmute",1):imgChange("vmute",0);
}

function imgrnd() {
	blnRndPlay?imgChange("pmode", 1):imgChange("pmode", 0);
}

function imgrept() {
	blnRept?imgChange("rept", 1):imgChange("rept", 0);
}

function imgpauz(f) {
	var rps=playState();
	if(f==2) {
		imgChange("pauzt",2);
	} else { 
		rps==4 ? imgChange("pauzt",1) : imgChange("pauzt",0);
	}
}

function imgplay(f) {
	var rps=playState();
	if(f==2) {
		imgChange("playt",2);
	} else {
		rps==3 ? imgChange("playt",1) : imgChange("playt",0);
	}
}

function imgstop(f) {
 	var rps=playState();
 	if(f==2) {
 		imgChange("stopt",2);
 	} else { 
 		rps==0? imgChange("stopt",1) : imgChange("stopt",0);
	}
}
function initPlayer() {
	var re = new RegExp("(msie[^;^)^(]*)", "ig");
	if(navigator.userAgent.search(re) == -1) {
		alert("��ʹ��IE��������ʣ������޷�ʹ�ò��������ܣ�");
	}
	realobj = getbyid('SMusic');
	disp2 = getbyid('disp2');
	dTitle = getbyid('disp1');
 	dTitle.innerHTML="X-Space Music/Video Player";
 	blnRept ? imgChange('rept',1) : imgChange('rept',0);
	blnLoopTrk ? imgChange('tloop',1) : imgChange('tloop',0);
	blnRndPlay ? imgChange('pmode',1) : imgChange('pmode',0);
	mkSel();
}
function dspList() {
	musicList = getbyid('xspace-mmList');
	if(intMmCnt >0) {
		var list_num=0;
		getbyid('xspace-mmList').innerHTML='';
		for (var i=0; i < intMmCnt; i++) {
			list_num = i + 1;
			if(objMmInfo[i].selMm=="t") {
				elm=' <input type=checkbox  style="cursor:pointer;" onClick=chkItemSel('+ i +'); checked>';
			} else {
				elm = ' <input type=checkbox style="cursor:pointer;" onClick=chkItemSel('+ i +');>';
			}
			elm = elm + '&nbsp;' + list_num + '. ';
			elm = elm + '<a href=javascript:selPlPlay(' + i + ');';
			elm = elm + ' onclick=\"this.blur();\">';
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

/**
 * �� Multi-object-contents���鸽ֵ
 */
function mkList(u, t,s, f) {
	var cu = u;
	var ct = t;
	var cs = s;
	var cf = f;
	var idx = 0;
	if(objMmInfo == null) {
		objMmInfo = new Array();
		idx = 0;
	} else {
		idx = objMmInfo.length;
	}
	if(u == "" || u == null) {cu = "";}
	if(t == "" || t == null) {ct = "nontitle";}
	if(f == "f" || f == "F") {
		cf = "f";
	} else{
		cf = "t";
		intSelMmCnt = intSelMmCnt+1;
	}
	blnUseSmi ? objMmInfo[idx] = new mkMmPath(cu,ct,cf,cs):objMmInfo[idx] = new mkMmPath(cu,ct,cf);
	intActMmCnt = intSelMmCnt;
	intMmCnt = objMmInfo.length;
}

/**
 * ������ѡȡ������Ŀ(Selected Media)������
 */
function mkSel() {
	arrSelMm = null;
	intSelMmCnt = 0;
	var selidx = 0;
	if(intMmCnt <= 0){intSMusicStat = 1; blnEnabled = false; return;} //�յĲ����б�
	arrSelMm = new Array();
	for(var i=0; i<intMmCnt; i++) {
		if(objMmInfo[i].selMm == "t") {
			arrSelMm[selidx] = i;
			selidx = selidx+1;
		}
	}
	intSelMmCnt = arrSelMm.length;

	if(intSelMmCnt <= 0) {
		blnEnabled = false;
		intSMusicStat = 2;
		arrSelMm = null;
		return;
	} else{
		blnEnabled = true;
		mkAct();	//������б�
	} 
}
/**
 * ���� Multi-object-contents ��������
 */
function mkMmPath(u, t, f, s) {
	this.mmUrl = u;
	var mmChkStr = u;
	mmChkStr = mmChkStr.toLowerCase();
	mmChkStr = mmChkStr.substring(mmChkStr.length - 4 , mmChkStr.length);
	switch(mmChkStr) {
		case ".asx":
			this.mmeta = "t";
			break;
		case ".wax":
			this.mmeta = "t";
			break;
		case ".wvx":
			this.mmeta = "t";
			break;
		case ".m3u":
			this.mmeta = "t";
			break;
		default:
			this.mmeta = "f";
 	}

	this.mmTit = t;
	this.mmDur = 0;
	this.selMm = f;
	this.actMm = f;
	if(blnUseSmi) { this.mmSmi=s; }
}
/**
 * ������ѡ��Ĳ����б�
 */
function mkAct() {
	arrActMm = null;
	intActMmCnt = 0;
	var selidx = 0;
	var actidx = 0;
	if(blnEnabled) {
		arrActMm = new Array();
		for(var i=0; i<intSelMmCnt; i++) {
			selidx = arrSelMm[i];
			if(objMmInfo[selidx].actMm == "t"){arrActMm[actidx] = selidx; actidx = actidx+1;}
		}
		intActMmCnt = arrActMm.length;
	} else {return;}
	if(intActMmCnt <= 0) {
		blnEOT = true;
		arrActMm = null;
	} else {blnEOT = false;}
}

/**
 * ����δѡ�񵽲����б��ý��
 */
function mkSelAct() {
	var idx = 0;
	for(var i=0; i<intSelMmCnt; i++) {
		idx = arrSelMm[i];
		objMmInfo[idx].actMm = "t";
 	}
	mkAct();
}

/**
 * ȫ��ѡȡ���еĲ����嵥��Ŀ
 */
function chkAllSel() {
	for(var i=0; i<intMmCnt; i++){
		objMmInfo[i].selMm = "t";
		objMmInfo[i].actMm = "t";
	}
	mkSel();
	dspList();
}

/**
 * ȡ�����е�ý��ѡ��
 */
function chkAllDesel() {
	for(var i=0; i<intMmCnt; i++) {
		objMmInfo[i].selMm = "f";
		objMmInfo[i].actMm = "f";
	}
	mkSel();
	dspList();
}

/**
 * ����ѡ��
 */
function reverseSel() {
	for(var i=0; i<intMmCnt; i++) {
		if(objMmInfo[i].selMm == "t") {
			objMmInfo[i].selMm = "f";
			objMmInfo[i].actMm = "f";
		} else {
			objMmInfo[i].selMm = "t";
			objMmInfo[i].actMm = "t";
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
		objMmInfo[idx].selMm = "f";
		objMmInfo[idx].actMm = "f";
	} else {
		objMmInfo[idx].selMm = "t";
		objMmInfo[idx].actMm = "t";
	}
	mkSel();
}

/**
 * ��ĳ�������ò�����Ŀ(Activated Media)����
 */
function chkItemAct(idx) {
	objMmInfo[idx].actMm = "f";
	mkAct();
}
/**
 * ý���б�
 */
function playSel() {
	playStop();
	startSMusic();
}
/**
 * �������ò�����Ŀ���С��Զ��������š��Ĵ���
 * ���Ǹ������� blnAutoProc ���趨ֵ�������Ķ�����
 */
function playAuto() {
	var rps=playState();
	if(rps>10 && rps<0){return;}
	if(!blnAutoProc){playStop();return;}
	if(blnLonelyPlay){playStop(); return;} 
	if(rps == 3) return;
	if(blnEOT) {
		if(blnLoopTrk) {
			startSMusic();
		} else { playStop(); }
 	} else {
		if(blnRndPlay) {
			rndPlay();
		} else {
			var idx = intCurrActIdx;
	 		var blnFind = false;
			for(var i=0; i<intSelMmCnt; i++) {
				if(intCurrActIdx == arrSelMm[i]) {
					idx = i+1;
					blnFind = true;
				}
			}
			if(!blnFind) { return; }
			if(idx >= intSelMmCnt) {
				idx=0;
				intCurrActIdx = arrSelMm[idx];
			} else { intCurrActIdx = arrSelMm[idx]; }
			selMmPlay(intCurrActIdx); 
		}
	}
}

function readyDrag(e) {
	if(document.layers){return false;}
	if(typeof e.srcElement != 'undefined') {
		evtSrcObj = e.srcElement.id;
	} else {
		evtSrcObj = e.target.id;
	}
	switch(evtSrcObj) {
		case "volSlider" :
			mPosX = e.clientX;
			sliderPosLeft = parseInt(volSliderObj.style.left);
			blnDragging = true;
			eobj = e;
			document.onmouseup = setVol;
			volSliderObj.onmousemove = volTracking;
			break;
		default :
			return false;
	}
}

function setVol() {
	blnDragging = false;
}

/**
 * ѭ������
 */
function chgTrkLoop() {
	var rps=playState();
	if(rps == 3) { clearInterval(tidTLab); }
	if(blnLoopTrk) {
		blnLoopTrk = false;
		imgChange('tloop', 0);
		disp2.innerHTML = "��ѭ������";
	} else {
		blnLoopTrk = true;
		imgChange('tloop', 1);
		disp2.innerHTML = "ѭ������";
	}
	tidMsg = setTimeout('rtnTLab()',1000);
}

/**
 * ������һ��
 */
function playPrev() {
	var rps= playState();
	if(rps == 3 || rps == 5) { playStop() }
	blnLonelyPlay = false;
	if(!blnEnabled) { waitMsg();return; }
	if(blnEOT) { mkSelAct(); }
	if(blnRndPlay) {
		rndPlay();
	} else {
		var idx = intCurrActIdx;
 		var blnFind = false;
		for(var i=0; i<intSelMmCnt; i++) {
			if(intCurrActIdx == arrSelMm[i]) {
				idx = i-1;
				blnFind = true;
			}
		}
		if(!blnFind) { startSMusic();return; }
		if(idx<0) {
			idx = intSelMmCnt-1;
			intCurrActIdx = arrSelMm[idx];
		} else { intCurrActIdx = arrSelMm[idx]; }
		selMmPlay(intCurrActIdx);
	}
}
/**
 * ����ʹ�����ڲ����嵥������ѡ�ĵ�һ��Ŀ
 */
function selPlPlay(idx) {
	var rps= playState();
	if(rps==3 || rps==5) {playStop();}
 	blnLonelyPlay = false;
	selMmPlay(idx);
}
/**
 * ������һ��ý��
 */
function playNext() {
	var rps= playState();
	if(rps==3 || rps==5) { playStop() }
	blnLonelyPlay = false;
 	if(!blnEnabled){waitMsg();return;}
 	if(blnEOT){mkSelAct();}
	if(blnRndPlay) {
		rndPlay();
	} else{
		var idx=intCurrActIdx;
 		var blnFind=false;
		for(var i=0; i<intSelMmCnt; i++) {
			if(intCurrActIdx == arrSelMm[i]) {
				idx=i+1;
				blnFind=true;
			}
		}
		if(!blnFind) { startSMusic();return; }
		if(idx>=intSelMmCnt) {
			idx=0;
			intCurrActIdx = arrSelMm[idx];
		} else {
			intCurrActIdx = arrSelMm[idx];
		}
		selMmPlay(intCurrActIdx);
	}
}

/**
 * ѡ�񲥷�ģʽ�������˳�򲥷�
 */
function chgPMode() {
	var wmps = playState();
	if(wmps == 3) { clearInterval(tidTLab); }
	if(blnRndPlay) {
		realobj.SetShuffle(false);
		blnRndPlay = false;
		imgChange('pmode', 0);
		disp2.innerHTML = "˳�򲥷�";
	} else {
		realobj.SetShuffle(true);
		blnRndPlay = true;
		imgChange('pmode', 1);
		disp2.innerHTML = "�������";
	}
	tidMsg=setTimeout('rtnTLab()', 1000); 
}