<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	主题足迹清理

	$RCSfile: cleantracks.php,v $
	$Revision: 1.1 $
	$Date: 2006/08/10 08:52:36 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//删除30天之前的主题足迹
$_SGLOBAL['db']->query('DELETE FROM '.tname('tracks').' WHERE dateline <'.($_SGLOBAL['timestamp']-30*24*3600));

?>