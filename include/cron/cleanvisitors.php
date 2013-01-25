<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	访客足迹清理

	$RCSfile: cleanvisitors.php,v $
	$Revision: 1.1 $
	$Date: 2006/08/10 08:52:36 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//删除30天之前的访客足迹
$_SGLOBAL['db']->query('DELETE FROM '.tname('visitors').' WHERE dateline <'.($_SGLOBAL['timestamp']-30*24*3600));

?>