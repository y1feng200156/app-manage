
var displayMonth,displayYear,displayValue,timeswitch,tformat,formElement;

/**
 * Created on 2006-3-24 9:54:10 by peng E-mail��andy@discuz.com
 * 
 * �˷���ʵ����:����ʱ���ѡ����,����д��������ڸ�ʽ��ţ�
 * ��������и����ĸ�ʽ����ʱ���ʽ�������޸�setDay()������չ�������ڸ�ʽ
 * 
 * @param string formElt ��дʱ����Ψһ���ƻ�idֵ�ַ���
 * @param Object e �¼�����Event
 * @param string format �������ڸ�ʽ��ѡ�����ʽ����3�࣬��������չ��ֻ��Ҫ����YYYY-mm-dd�����ڸ�ʽ��������д�˲���
 * 										10��20��֮�����ֻ�����ڸ�ʽ�ķ����ַ�������
 * 														0: ������YYYY-mm-dd
 * 														11��������YYYY��mm��dd��
 * 														12������YYYY/mm/dd
 * 										20��30��֮�����ֻ�����ڸ�ʽ�ķ����ַ�������
 * 														21��YYYY-mm-dd HH:ii:ss
 * 														22��YYYY��mm��dd�� HHʱii��ss��
 * @return ���û�д����ʽ�����Ĭ�Ϸ���YYYY-mm-dd�����ڸ�ʽ������format��ŷ������ڸ�ʽ
 * @version 1.0
 */
function getDatePicker(formElt,e,format) {
	formElement=getobject(formElt);
	tformat=(!format ? 0:format);
//	var pElement=(!pElt ? document.body:getobject(pElt));
	var pElement=document.body;
	var div=document.getElementById("divHide");
  	if(!div) {
  		div = document.createElement("DIV");
  		div.id = "divHide";
  		div.style.cssText = "left:0;top:0;overflow:hidden;position:absolute;background-color:#FFF;visibility:hidden;border:1px solid #FFBE00;";
		pElement.appendChild(div);
	}
	timeswitch = 20;
	if(tformat < timeswitch) {
		div.onblur=hidePicker;	//����ʱ���ʽʱ��������뿪��ʱ�����¼�
	}

	var time,dateValue=formElement.value;
	if(dateValue!='') {
		var re=new RegExp("-","g"); 
		dateValue=dateValue.replace(re,'/');
		time=new Date(dateValue);
	}
	if(!time || time=='NaN')time=new Date();
	displayMonth = time.getMonth();
	displayYear = time.getFullYear();
	displayValue = time;
	
	var style=div.style;
	var width=200,height=155;
	
	//����Div�ڴ����е�λ��
	style.left=(e.pageX?e.pageX:e.x+document.documentElement.scrollLeft)+"px";
	style.top=(e.pageY?e.pageY:e.y+document.documentElement.scrollTop)+"px";
	style.width=width+"px";
	style.height=height+"px";
		
	showPicker();
	style.visibility="visible";
	div.focus();
	drag(div);
}

function hidePicker() {
	var div=document.getElementById("divHide");
	div.style.visibility = 'hidden';
}

function showPicker() {
	/*********��ʼ����ǰʱ��************/ 
	var date;
	//�ж�Ŀ�����Ƿ���ֵ
	if(typeof displayValue == "object" ) {
		date = displayValue;
	} else {
		date=new Date();
	}
	var isThisYear = (displayYear == date.getFullYear());
	var isThisMonth = (isThisYear && displayMonth == date.getMonth());
	var today=(isThisYear && isThisMonth ? date.getDate():-1); 
	hour = date.getHours();
	second = date.getSeconds();
	minutes = date.getMinutes();
	var months = new Array("һ��", "����", "����", "����", "����", "����", "����","����", "����", "ʮ��", "ʮһ��", "ʮ����");

	var html="<table id=tablePicker width=100% cellspacing=0 cellpadding=0 borderColorLight=#FFBE00 borderColorDark=#FFBE00 border=0 bgcolor=#ffffff style='font-size:12px;font-family:����;table-layout:fixed'>";
	html+="<tr height=20px borderColor=#FFBE00 bgColor=#FFBE00 align=center><td colSpan=7 style='padding-top:3px;cursor:move'>";
	//display year
	html+="<font style=\"cursor:pointer\" onmousedown=\"incYear(-1)\" color=white><img src=\"./admin/images/back.gif\" /></font>&nbsp";
	html+="<font color=" + (isThisYear ? "green":"#ED1C24") + ">" + displayYear + "��</font>";
	html+="&nbsp<font style='cursor:pointer' onmousedown=\"incYear(1)\" color=white><img src=\"./admin/images/next.gif\" /></font>&nbsp;&nbsp;&nbsp;";
	//display month
	html+="<font style=\"cursor:pointer\" onmousedown=\"incMonth(-1)\" color=white><img src=\"./admin/images/back.gif\" /></font>&nbsp";
	if(displayMonth<10)html+="&nbsp";
	html+="<font color=" + (isThisMonth ? "green":"#ED1C24") + ">" + months[displayMonth] + "</font>";
	if(displayMonth<10)html+="&nbsp";
	html+="&nbsp<font style=\"cursor:pointer\" onmousedown=\"incMonth(1)\" color=white><img src=\"./admin/images/next.gif\" /></font>";
	//Ŀ�������Ϊֻ��ʱ��ʾ�����ť
	if(formElement.readOnly) {
		//�ж�������Ƿ�Ϊֻ��������ǵĻ�����ʾ�����ť
		html+="&nbsp;<span onclick='formElement.value=\"\";hidePicker();' style=\"cursor:pointer;\"><img src=\"./admin/images/btn_clear.gif\" /></span>";	
	}
	if(tformat > timeswitch) {
		html+="&nbsp;<span onclick='hidePicker();' style=\"cursor:pointer;\"><img src=\"./admin/images/btn_close1.gif\" /></span>";	
	}
	html+="</td></tr>";
	//display day
	html+="<tr height=20px align=center><td style=\"padding-top:3px;BORDER-LEFT:none;BORDER-RIGHT:none\"><font color=\"#ED1C24\">��</font></td><td class=week>һ</td><td class=week>��</td><td class=week>��</td><td class=week>��</td><td class=week>��</td><td class=week><font color=\"green\">��</font></td</tr>";

	var startDay=new Date(displayYear,displayMonth,1).getDay();
	var dayOfMonthOfFirstSunday = (7 - startDay + 1);
	var intDaysInMonth=getDays(displayMonth, displayYear);
	for (var intWeek = 0; intWeek < 6; intWeek++) {
		html+="<tr height=18px align=center>";
		var dayOfMonth;
	    for (var intDay = 0; intDay < 7; intDay++) {
	     	dayOfMonth = (intWeek * 7) + intDay + dayOfMonthOfFirstSunday - 7;
		 	if (dayOfMonth <= 0) {
	          		html+="<td height=18px borderColor=#ffffff>&nbsp</td>";
			} else if(dayOfMonth <= intDaysInMonth) {
		   		html+="<td borderColor=#ffffff "+(dayOfMonth==today ? "bgcolor=#ffcc66":"")+" onmousedown=\"setDay(" + dayOfMonth + ")\"";
		   		html+="style=\"cursor:pointer;color:" + (dayOfMonth==today ? "red":"black") + "\">" + dayOfMonth + "</td>";
			}
		}
		if(intWeek+1==6 && tformat > timeswitch)
			html+="<td colspan='7' borderColor=#ffffff><div style='margin:-1.5px;'><input maxlength='2' onmousedown='this.select()' name='hour' id='hour' style='border: 1px solid #CCCCCC;font-size:12px;font-family:����;line-height:12px;height:12px;width:20px;text-align:center;padding:2px;' size='1' value='"+ (hour<10?'0'+hour:hour) +"' type='text'> ʱ <input maxlength='2' onmousedown='this.select()' value='"+ (minutes<10?'0'+minutes:minutes) +"' style='border: 1px solid #CCCCCC;font-size:12px;font-family:����;line-height:12px;height:12px;text-align:center;width:20px;padding:2px;' name='minutes' size='1' type='text'> �� <input maxlength='2' onmousedown='this.select()' name='second' style='border: 1px solid #CCCCCC;font-size:12px;line-height:12px;height:12px;text-align:center;width:20px;padding:2px;' value='"+ (second<10?'0'+second:second) +"' size='1' type='text'> ��<div></td>";
		html+="</tr>";
	}
	html+="</table>";
	document.getElementById("divHide").innerHTML=html;
}

function getDays(month, year) {
	if (1==month)return ((0 == year % 4) && (0 != (year % 100))) || (0 == year % 400) ? 29 : 28;
	var daysInMonth = new Array(31, 28, 31, 30, 31, 30, 31, 31,30, 31, 30, 31);
	return daysInMonth[month];
}

function setDay(day) {
	//����������ʱ����
	var y,m,d,h,i,s;
	y = displayYear;
	m = (displayMonth<9 ? "0":"") + (displayMonth+1);
	d = (day<10 ? "0":"") + day;
	if(tformat && tformat > timeswitch) {
		re = /[^\d]/g;            // ����������ʽģʽ��
		hour = document.getElementsByName("hour")[0];
		minutes = document.getElementsByName("minutes")[0];
		second = document.getElementsByName("second")[0];
		if(re.test(hour.value) || Math.abs(parseInt(hour.value))>23){
			alert("ʱ�����Сʱֻ����0��23������");
			hour.focus();
			hour.select();
			return false;
		}
		if(re.test(minutes.value) || Math.abs(parseInt(minutes.value))>59){
			alert("ʱ���������ֻ����0��59������");
			minutes.focus();
			minutes.select();
			return false;
		}
		if(re.test(second.value) || Math.abs(parseInt(second.value))>59){
			alert("ʱ���������ֻ����0��59������");
			second.focus();
			second.select();
			return false;
		}
		h = (hour.value.length < 2 ? "0" : "") + hour.value;
		i = (minutes.value.length < 2 ? "0":"") + minutes.value;
		s = (second.value.length < 2 ? "0":"") + second.value;
	}
	var formEV="";

	//����Ҫ���ص����������������
	if(tformat == 0) {
		formEV = y + '-' + m + '-' + d;
	} else if(tformat == 11) {
		formEV = y + '��' + m + '��' + d + '��';
	} else if(tformat == 12) {
		formEV = y + '/' + m + '/' + d;
	} else if(tformat == 21) {
		formEV = y + '-' + m + '-' + d + ' ' + h + ':' + i + ':' + s;
	} else if(tformat == 22) {
		formEV = y + '��' + m + '��' + d + '�� ' + h + 'ʱ' + i + '��' + s + '��';
	}
	formElement.value = formEV;
	formElement.focus();
	hidePicker();
}
/**
 * �·�����������
 */
function incMonth(delta) {
	displayMonth+=delta;
	if (displayMonth>=12) {
		displayMonth = 0;
		incYear(1);
	} else if(displayMonth<=-1) {
	     displayMonth = 11;
	     incYear(-1);
	} else {
		showPicker();
	}
}
/**
 * �������������
 */
function incYear(delta,eltName) {
	displayYear+=delta;
	showPicker();
}

/**
 * ��ȡ����ID��name���ַ�������ȡ����
 * 
 * @param string ����ID������
 * @return mixed null��ʾû���ҵ�����object ��ʾ�ҵ����Ӧ�Ķ���
 */
function getobject(idname) {
	if (document.getElementById) {
		return document.getElementById(idname);
	} else if (document.all) {
		return document.all[idname];
	} else if (document.layers) {
		return document.layers[idname];
	} else {
		return null;
	}
}

/**
 * ʵ�ֲ����ҷ����
 */
function drag(o){
    o.onmousedown = function(a){
        var d = document;
        if(!a) a = window.event;
        if(o.setCapture)
            o.setCapture();
        else if(window.captureEvents)
            window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);

		var re = new RegExp("px","ig");
		//���㵱ǰ��������������ڲɼ�������������ģؼ��
        var x = (a.pageX?a.pageX:a.x) - parseInt(o.style.left.replace(re,""));
        //���㵱ǰ��������������ڲɼ��������������Y���
        var y = (a.pageY?a.pageY:a.y) - parseInt(o.style.top.replace(re,""));
        
        d.onmousemove = function(a){
            if(!a)a=window.event;
            if(!a.pageX)a.pageX=a.clientX;
            if(!a.pageY)a.pageY=a.clientY;
            var tx=a.pageX-x,ty=a.pageY-y;
            o.style.left = tx + "px";
            o.style.top = ty + "px";
        };
        
        d.onmouseup=function(){
            if(o.releaseCapture)
                o.releaseCapture();
            else if(window.captureEvents)
                window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);
            d.onmousemove=null;
            d.onmouseup=null;
        };
    };
}