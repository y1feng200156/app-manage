var current = 0;	//��ǰ
var fadeimg = 0; 	//ԭѡ���ͼƬ
var nonceidx = 0;	//����λ��ͼƬ�������е�λ��
var along, next;
var objTimer;
var ie = navigator.userAgent.toLowerCase().indexOf('ie');	//�ж��Ƿ�IE
nereidFadeObjects = new Object();
nereidFadeTimers = new Object();

//��ȡһ������
function getEById(str) {
	if (document.getElementById) {
		return document.getElementById(str);
	} else if (document.all) {
		return document.all[str];
	} else if (document.layers) {
		return document.layers[str];
	} else {
		return null;
	}
}

//�ж��Ƿ����
if(typeset) {
	upleftcss = "xspace-reelScrollButton xspace-reelScrollUpButton";
	downrightcss = "xspace-reelScrollButton xspace-reelScrollDownButton";
} else {
	upleftcss = "xspace-reelScrollButton xspace-reelScrollLeftButton";
	downrightcss = "xspace-reelScrollButton xspace-reelScrollRightButton";
}

//�ж�shownum�Ƿ����ͼƬ����
if(shownum > picarray.length) {
	shownum = picarray.length;
}

//�����ⲿ�Ƿ��д������ͼƬ��λ��
//if(nowshow && (nowshow < picarray.length)) {
//	//�жϼ���ͼƬ��λ��
//	if(nowshow+shownum < picarray.length) {
//		nonceidx = nowshow;
//	} else if(nowshow+shownum >= picarray.length ) {
//		nonceidx = nowshow - (shownum - (picarray.length - nowshow));
//		current = fadeimg = nowshow - nonceidx;
//	}
//} else if(nowshow >= picarray.length) {
	nowshow = 0;
//}


//д����ʽ
document.write('<style type="text/css">');
if(typeset) {
	document.write('.xspace-reelScrollButton img {width: '+ picwidth +'px; height: 10px;}');
} else {
	document.write('.xspace-reelScrollButton img {width: 15px; height: '+ picheight +'px;}');
}
document.write('</style>');

if(0<shownum) {
	document.writeln('<ul id="xspace-itempiclist" onmouseover="showonoff(true)" onmouseout="showonoff(false)">');
	document.writeln('<li id="upleft"><a class="'+upleftcss+'" onclick="popimg(\'along\')" href="javascript:;"><img src="'+siteUrl+'/images/base/blank.gif" alt="" /></a></li>');
	//����б�
	for(i=0;i<shownum;i++) {
		document.write('<li>');
		document.writeln('<img src="" width="'+ picwidth +'" height="'+ picheight +'" onclick="selectimg('+i+')" id="zoomimg'+ i +'" onmouseover="hFade(this,100,10,4)" onmouseout="hFade(this,50,10,4)" alt="" />');
		document.write('</li>');
	}
	document.writeln('<li id="downright"><a class="'+downrightcss+'" onclick="popimg(\'next\')" href="javascript:;"><img src="'+siteUrl+'/images/base/blank.gif" alt="" /></a></li>');
	document.writeln('</ul>');
}
refreshimg();

/**
* ��ť����
* @param boolean bool: true:��ʾ false:����ʾ
*/
function showonoff(bool) {
	if(bool == true) {
		getEById('upleft').className = 'xspace-on';
		getEById('downright').className = 'xspace-on';
	} else {
		getEById('upleft').className = '';
		getEById('downright').className = '';
	}
}

function hFade(object, destOp, rate, delta) {
	re = new RegExp("zoomimg","ig"); 
 	if(current==parseInt((object.id).replace(re, "")))
 		return;
 	nereidFade(object, destOp, rate, delta);
}

function nereidFade(object, destOp, rate, delta) {
	if (typeof object != "object"){ 
		setTimeout("nereidFade("+object+","+destOp+","+rate+","+delta+")",0);
		return;
	}
	clearTimeout(nereidFadeTimers[object.sourceIndex]);
	var diff, direction = 1;
	//���ݲ�ͬ�������ѡ��ͬ���˾�����
	if(typeof object.filters == "object") {
		diff = destOp-object.filters.alpha.opacity;
		if (object.filters.alpha.opacity > destOp){
			direction = -1;
		}
		delta=Math.min(direction*diff,delta);
		object.filters.alpha.opacity+=direction*delta;
		if (object.filters.alpha.opacity != destOp){
			nereidFadeObjects[object.sourceIndex]=object;
			nereidFadeTimers[object.sourceIndex]=setTimeout("nereidFade(nereidFadeObjects["+object.sourceIndex+"],"+destOp+","+rate+","+delta+")",rate);
		}
	} else {
		var dop = destOp == 100?1:0.5;
		rate=0;
		if(!parseFloat(object.style.opacity)) {
			object.style.opacity = 0.5;
		}
		if(dop == 1) {
			object.style.opacity=parseFloat(object.style.opacity) + parseFloat(0.1);
		} else {
			object.style.opacity=parseFloat(object.style.opacity) - parseFloat(0.1);
		}
		if(parseFloat(object.style.opacity) < 0.5 || parseFloat(object.style.opacity) > 1 )
			return;
		if (object.style.opacity != dop){
			nereidFadeObjects[object.sourceIndex]=object;
			nereidFadeTimers[object.sourceIndex]=setTimeout("nereidFade(nereidFadeObjects["+object.sourceIndex+"],"+destOp+","+rate+","+delta+")",rate);
		}
	}
}

/**
* ѡ��ͼƬ
*/
function selectimg(i) {
	if (ie > -1) {
		document.getElementById("zoomimg"+fadeimg).style.cssText="filter: alpha(opacity=50);";
	} else {
		document.getElementById("zoomimg"+fadeimg).style.cssText="opacity:0.5;";
	}
	//����ǰλ�õ�ͼƬ�Ļ��˾�Ч��
	currentfilter(false);
 	current = i;
 	fadeimg = i;
 	currentfilter(true);
	var loadurl = picarray[document.getElementById("zoomimg"+i).pid][2];
	if(showmode) {
 		window.open(loadurl)
	} else {
		document.getElementById("xspace-imgshowbox").innerHTML= "<a href='"+loadurl+"' title='����鿴ԭʼͼƬ' target='_blank'><img id='xspace-showimg' src='"+loadurl+"' alt='' pid='"+document.getElementById("zoomimg"+i).pid+"' \/><\/a>";
	}
	//�жϵ�ǰѡ���ͼƬ�Ƿ�λ���б��еĵ�һ�Ż����һ�ţ��Ĺ��ǣ������ƻ�����һ��
	var refresh = false;
	if(current == 0) {
		if(nonceidx-1 >= 0) {
			refresh = true;
			nonceidx--;
			if(current + 1 < shownum)
				current++;
		}
	} else if(current == shownum-1) {
		if(nonceidx+shownum < picarray.length) {
			refresh = true;
			nonceidx++;
			if(current - 1 >= 0)
				current--;
		}
	}
	if(refresh) {
		refreshimg();
	}
}

//function modifyidx(idx) {
//	alert("B:"+idx);
//	idx = idx - picarray.length;
//	alert("E:"+idx);
//	if(idx > picarray.length) {
//		
//		modifyidx(idx);
//	}
//	return idx;
//}
/**
 * ���������б�ͼƬ
 */
function refreshimg() {
	var idx = 0;
	for(i=0;i<shownum;i++) {
		idx = nonceidx + i;
		//�жϵ�ǰ�α��Ƿ�Խ�磬���������α���0
		while(idx >= picarray.length) {
			idx = idx -picarray.length;
		}
		if(idx<picarray.length) {
			var obj = getEById("zoomimg"+i);
			obj.alt = picarray[idx][1];
			obj.pid = idx;
			obj.src = picarray[idx][0];
		}
	}
	currentfilter(true);
}

/**
 * ���ơ�����ͼƬ�����һ��ͼ
 * @param string obj: next����һ�š�along����һ��
 */
function popimg(button) {
	//����ǰλ�õ�ͼƬ�Ļ��˾�Ч��
	currentfilter(false);
	var picnum = picarray.length;
	var movenum = nonceidx+shownum;
	if(button == 'next') {
		//if(movenum < picnum) {
			nonceidx++;
			if(current - 1 >= 0) {
				current--;
			}
//		}
	} else if(button == 'along') {
		if(nonceidx-1 >= 0) {
			nonceidx--;
			if(current + 1 < shownum)
				current++;
		}
	}
	refreshimg();
	currentfilter(true);
	//�жϵ�ǰѡ���ͼƬ�Ƿ��Ƴ��б�������Ƴ��б������¼�����ͼƬ
	if((current==0 || current==shownum-1) && document.getElementById(imgid) != null) {
		document.getElementById("xspace-imgshowbox").innerHTML= "<a href='"+picarray[document.getElementById("zoomimg"+current).pid][2]+"' title='����鿴ԭʼͼƬ' target='_blank'><img id='xspace-showimg' src='"+picarray[document.getElementById("zoomimg"+current).pid][2]+"' alt='"+picarray[document.getElementById("zoomimg"+current).pid][1]+"' \/><\/a>";
	}
}
/**
* ͸���ȵ���
*/
function currentfilter(mtype) {
	if(getbyid("zoomimg"+current) == null) return false;
	if(mtype == true) {
		if (ie > -1) {
			document.getElementById("zoomimg"+current).style.cssText = "filter: alpha(opacity=100);";
		} else {
			document.getElementById("zoomimg"+current).style.cssText = "opacity:1;";
		}
	} else if(mtype == false) {
		if (ie > -1) {
			document.getElementById("zoomimg"+current).style.cssText = "filter: alpha(opacity=50);";
		} else {
			document.getElementById("zoomimg"+current).style.cssText = "opacity:0.5;";
		}
	}
}

/**
*�Զ�����
**/
function pagedown(time) {
	window.clearTimeout(objTimer);
	if(time) {
		objTimer = window.setTimeout("pagedown("+time+")",time);
		popimg('next');
	}
}
