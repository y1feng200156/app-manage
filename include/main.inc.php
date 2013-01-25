<?php

/*
	[SupeSite/X-Space] common.inc.php
	基本包含文件

	$RCSfile: main.inc.php,v $
	$Revision: 1.72.4.13 $
	$Date: 2007/06/22 13:23:55 $
*/

@define('IN_SUPESITE', TRUE);
define('S_ROOT', substr(dirname(__FILE__), 0, -7));
define('S_VER', '5.5.2');
define('X_VER', '3.0.2');
define('S_RELEASE', '20070622');
define('ID_KEY',"ID_PASSWORD");//定义加密密钥
define('D_BUG', '0');

D_BUG?error_reporting(E_ALL):error_reporting(E_ERROR);

$_SGLOBAL = $_SBLOCK  = $_SCONFIG = $_SHTML = $_DCACHE = $_SGET = array();

//当前权限操作名称
$CurrentAuthOp = "currentAuthOp";
if(!(get_magic_quotes_gpc())) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
    $_COOKIE = saddslashes($_COOKIE);
}

$mtime = explode(' ', microtime());
$_SGLOBAL['supe_starttime'] = $mtime[1] + $mtime[0];
$_SGLOBAL['timestamp'] = time();

include_once(S_ROOT.'./config.php');

define('S_URL', $siteurl);
define('B_URL', $bbsurl);
define('B_VER', $bbsver);
define('COVER_DIR',$shopcoverimgdir);//商品封面目录
define('AD_DIR',$adimgdir);//广告图片目录
define('ISO_DIR',$isodir);//iso图片目录
define('SHOW_DIR',$shopimagedir);//展示图片目录
define('VIDEO_DIR',$shopvideodir);//展示视频目录
define('LOGO_DIR',$logodir);//展示视频目录

@include_once(S_ROOT.'./data/system/config.cache.php');

$_SCONFIG = $_SSCONFIG;

if(!empty($headercharset)) {
	header('Content-Type: text/html; charset='.$charset);
}

//ONLINE IP
if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$_SGLOBAL['onlineip'] = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$_SGLOBAL['onlineip'] = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$_SGLOBAL['onlineip'] = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$_SGLOBAL['onlineip'] = $_SERVER['REMOTE_ADDR'];
}
preg_match("/[\d\.]{7,15}/", $_SGLOBAL['onlineip'], $onlineipmatches);
$_SGLOBAL['onlineip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
unset($onlineipmatches);

$_SERVER['HTTP_USER_AGENT'] = empty($_SERVER['HTTP_USER_AGENT'])?'':$_SERVER['HTTP_USER_AGENT'];
$_SGLOBAL['authkey'] = md5($_SCONFIG['sitekey'].$_SERVER['HTTP_USER_AGENT']);

define('H_DIR', $_SCONFIG['htmldir']);
if(substr($_SCONFIG['htmldir'], 0, 2) == './' && empty($_SCONFIG['htmlurl'])) {
	$_SCONFIG['htmlurl'] = S_URL.substr($_SCONFIG['htmldir'], 1);
}
define('H_URL', $_SCONFIG['htmlurl']);

if ($_SCONFIG['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

$bbsattachurl = empty($bbsattachurl)?$bbsurl.'/attachments':$bbsattachurl;
define('B_A_URL', $bbsattachurl);

$_SCONFIG['adminemail'] = $adminemail;
$_SCONFIG['dbreport'] = $dbreport;
$_SCONFIG['charset'] = $charset;

$_SCONFIG['tplrefresh'] = $tplrefresh;//风格模板自动刷新开关

$_SGLOBAL['tpl_blockvalue'] = array();
$_SGLOBAL['debug_query'] = array();

define('A_DIR', $_SCONFIG['attachmentdir']);
if(substr($_SCONFIG['attachmentdir'], 0, 2) == './' && empty($_SCONFIG['attachmenturl'])) {
	$_SCONFIG['attachmenturl'] = S_URL.substr($_SCONFIG['attachmentdir'], 1);
}
define('A_URL', $_SCONFIG['attachmenturl']);

$newsiteurl = S_URL;
if(strpos($newsiteurl, '://') === false) {
	$newsiteurl = 'http://'.(empty($_SERVER['HTTP_HOST'])?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST']).$newsiteurl;
}
define('S_URL_ALL', $newsiteurl);

if(empty($nolanguage)) include_once(S_ROOT.'./language/main.lang.php');
include_once(S_ROOT.'./function/main.func.php');

//获取频道信息
//$channels = getchannels();

function saddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

?>