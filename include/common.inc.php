<?php

/*
	[SupeSite/X-Space] common.inc.php
	全局包含文件

	$RCSfile: common.inc.php,v $
	$Revision: 1.71 $
	$Date: 2007/05/10 15:06:34 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

define('DISCUZ_PARTNER', '2088002052150939');
define('DISCUZ_SECURITYCODE', 'gh0bis45h89m5mwcoe85us4qrwispes0');
define("DEBUG",TRUE);
dbconnect();

include_once(S_ROOT.'/function/common.func.php');
//@include_once('./data/system/ad.cache.php');

//@include_once('./data/system/cron.cache.php');
if(empty($_SGLOBAL['cronnextrun']) || $_SGLOBAL['cronnextrun'] <= $_SGLOBAL['timestamp']) {
	include_once(S_ROOT.'./function/cron.func.php');
	runcron();
}

//个人空间模块
$_SGLOBAL['spaceblocks'] = array('guestbook','comment','favorite','track','visitor','friend','userlink','bbsreply','bbsforum','bbsfavorite','newblog','newimage','newvideo','newgoods','newfile','newlink','group','mythread','myblog','newitem','itemtype','itemtype_blog','itemtype_image','itemtype_video','itemtype_goods','itemtype_file','itemtype_link');

?>