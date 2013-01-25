<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	生成昨日存档

	$RCSfile: makearchiver.php,v $
	$Revision: 1.8 $
	$Date: 2007/05/10 15:06:34 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

ignore_user_abort();//后台运行
set_time_limit(0);//设置超时时间

$archiverdir = S_ROOT.'./archiver';

$nowdayf = sgmdate($_SGLOBAL['timestamp']-3600*24, 'Y-m-d');
$dateline1 = sstrtotime($nowdayf);
$dateline2 = sstrtotime(sgmdate($_SGLOBAL['timestamp'], 'Y-m-d'));

global $lang;

$thecontent = '';

//子页面头写入
$subfp = fopen($archiverdir.'/'.$nowdayf.'.html', 'w');

$subfpheader = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset='.$_SCONFIG['charset'].'" />
	<meta name="keywords" content="'.$_SCONFIG['sitename'].' '.$nowdayf.' '.$lang['archiver'].'" />
	<meta name="description" content="'.$_SCONFIG['sitename'].' '.$nowdayf.' '.$lang['archiver'].'" />
	<title>'.$nowdayf.' - '.$_SCONFIG['sitename'].' - '.$lang['archiver'].' - Powered by SupeSite / X-Space</title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em;}
	body { background: #F5FBFF; }
	.bodydiv { margin: 2em auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; padding:1em; }
	h1 { font-size: 18px; background: #E8F7FC; color: #5086A5; text-align:center; padding:0.5em; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>'.$_SCONFIG['sitename'].' '.$nowdayf.' '.$lang['archiver'].'</h1>
	<p>'.$lang['your_position'].': <a href="'.S_URL.'/">'.$_SCONFIG['sitename'].'</a> &raquo; <a href="'.S_URL.'/archiver/">'.$lang['archiver'].'</a> &raquo; <a href="'.$nowdayf.'.html">'.$nowdayf.'</a>
	</p>
	<ul>
	';
fwrite($subfp, str_replace("\t", '', $subfpheader));

//昨日发布信息
$query = $_SGLOBAL['db']->query("SELECT dateline, subject, itemid, type, username, uid FROM ".tname('spaceitems')." WHERE folder='1' AND dateline>'$dateline1' AND dateline<'$dateline2' ORDER BY dateline");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if($value['type'] == 'news') {
		$content = '[<a href="'.geturl("action/$value[type]", 2).'" target="_blank">'.$lang[$value['type']].'</a>] <a href="'.geturl("action/viewnews/itemid/$value[itemid]", 2).'" target="_blank">'.$value['subject'].'</a> (<a href="'.geturl("uid/$value[uid]", 2).'">'.$value['username'].'</a> '.sgmdate($value['dateline']).')';
	} else {
		$content = '[<a href="'.geturl("action/$value[type]", 2).'" target="_blank">'.$lang[$value['type']].'</a>] <a href="'.geturl("uid/$value[uid]/action/viewspace/itemid/$value[itemid]", 2).'" target="_blank">'.$value['subject'].'</a> (<a href="'.geturl("uid/$value[uid]", 2).'">'.$value['username'].'</a> '.sgmdate($value['dateline']).')';
	}
	
	if(empty($thecontent)) $thecontent = $content;
	
	//子页面写入
	fwrite($subfp, "<li>$content</li>\r\n");
}

//子页面结束
$fpstr = '</ul>
	<br>
	<center>
	<p><a href="'.S_URL.'/">'.$_SCONFIG['sitename'].'</a> <a href="'.S_URL.'/archiver/">'.$lang['archiver'].'</a> <a href="'.$nowdayf.'.html">'.$nowdayf.'</a>
	<br>Powered by <a href="http://www.supesite.com" target="_blank">SupeSite / X-Space</a>
	</p>
	</center>
	</div>
	</body>
	</html>';

fwrite($subfp, str_replace("\t", '', $fpstr));
fclose($subfp);

//主页面处理
$mainli = '<li><a href="'.$nowdayf.'.html">'.$nowdayf.' '.$lang['archiver'].'</a> '.$lang['the_first_theme'].': '.$thecontent.'</li>'."\r\n";

$writeli = '';
if(file_exists($archiverdir.'/index.html')) {
	$fcontent = '';
	if(@$fp = fopen($archiverdir.'/index.html', 'r')) {
		$fcontent = fread($fp, filesize($archiverdir.'/index.html'));
		fclose($fp);
	}
	
	preg_match_all("/\<li\>(.+?)\<\/li\>/is", $fcontent, $mathes);
	
	if(!empty($mathes[1])) {
		foreach ($mathes[1] as $value) {
			if(strpos($value, $nowdayf) === false) {
				$writeli .= "<li>$value</li>\r\n";
			}
		}
	}
}

//不存在
$ncontent = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset='.$_SCONFIG['charset'].'" />
	<meta name="keywords" content="'.$_SCONFIG['sitename'].' '.$lang['archiver'].'" />
	<meta name="description" content="'.$_SCONFIG['sitename'].' '.$lang['archiver'].'" />
	<title>'.$_SCONFIG['sitename'].' - '.$lang['archiver'].' - Powered by SupeSite / X-Space</title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em;}
	body { background: #F5FBFF; }
	.bodydiv { margin: 2em auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; padding:1em; }
	h1 { font-size: 18px; background: #E8F7FC; color: #5086A5; text-align:center; padding:0.5em; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>'.$_SCONFIG['sitename'].' '.$lang['archiver'].'</h1>
	<p>'.$lang['your_position'].': <a href="'.S_URL.'/">'.$_SCONFIG['sitename'].'</a> &raquo; <a href="'.S_URL.'/archiver/">'.$lang['archiver'].'</a> &raquo; <a href="'.geturl("action/rss").'">XML</a>
	</p>
	<ul>
	'.$writeli.'
	'.$mainli.'
	</ul>
	<br>
	<center>
	<script language="javascript">
	<!--
	function ShowDateFile(){
		if(document.forms.old_cheese.Year.selectedIndex == 0) { alert("'.$lang['please_choose_year'].'"); return false;}
		if(document.forms.old_cheese.Month.selectedIndex == 0){ alert("'.$lang['please_choose_the_month'].'"); return false;}
		if(document.forms.old_cheese.Day.selectedIndex == 0)  { alert("'.$lang['please_choose_the_date'].'"); return false;}
		year = document.forms.old_cheese.Year.options[document.forms.old_cheese.Year.selectedIndex].value;
		month = document.forms.old_cheese.Month.options[document.forms.old_cheese.Month.selectedIndex].value;
		day = document.forms.old_cheese.Day.options[document.forms.old_cheese.Day.selectedIndex].value;
		loadpos = year+"-"+month+"-"+day+".html";
		controlWindow = window.open(loadpos,"_blank");
		return false;
	}
	//--> 
	</script>
	<p>
	<form method="POST" action="--WEBBOT-SELF--" name="old_cheese">
	<select name="Year" size="1">
	<option value="'.$lang['please_choose_year'].'">'.$lang['please_choose_year'].'</option>
	<option value="2007">2007</option>
	<option value="2006">2006</option>
	</select>
	<select name="Month" size="1">
	<option value="'.$lang['please_choose_the_month'].'">'.$lang['please_choose_the_month'].'</option>
	<option value="01">1</option>
	<option value="02">2</option>
	<option value="03">3</option>
	<option value="04">4</option>
	<option value="05">5</option>
	<option value="06">6</option>
	<option value="07">7</option>
	<option value="08">8</option>
	<option value="09">9</option>
	<option value="10">10</option>
	<option value="11">11</option>
	<option value="12">12</option>
	</select>
	<select name="Day" size="1">
	<option value="'.$lang['please_choose_the_date'].'">'.$lang['please_choose_the_date'].'</option>
	<option value="01">1</option>
	<option value="02">2</option>
	<option value="03">3</option>
	<option value="04">4</option>
	<option value="05">5</option>
	<option value="06">6</option>
	<option value="07">7</option>
	<option value="08">8</option>
	<option value="09">9</option>
	<option value="10">10</option>
	<option value="11">11</option>
	<option value="12">12</option>
	<option value="13">13</option>
	<option value="14">14</option>
	<option value="15">15</option>
	<option value="16">16</option>
	<option value="17">17</option>
	<option value="18">18</option>
	<option value="19">19</option>
	<option value="20">20</option>
	<option value="21">21</option>
	<option value="22">22</option>
	<option value="23">23</option>
	<option value="24">24</option>
	<option value="25">25</option>
	<option value="26">26</option>
	<option value="27">27</option>
	<option value="28">28</option>
	<option value="29">29</option>
	<option value="30">30</option>
	<option value="31">31</option>
	</select>
	<input type="button" value="'.$lang['detect_specified_file'].'" onClick="ShowDateFile();return false;">
	</form>
	</p>
	<p><a href="'.S_URL.'/">'.$_SCONFIG['sitename'].'</a> <a href="'.S_URL.'/archiver/">'.$lang['archiver'].'</a>
	<br>Powered by <a href="http://www.supesite.com" target="_blank">SupeSite / X-Space</a>
	</p>
	</center>
	</div>
	</body>
	</html>';

$fp = fopen($archiverdir.'/index.html', 'w');
fwrite($fp, str_replace("\t", '', $ncontent));
fclose($fp);	

?>