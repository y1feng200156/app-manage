<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	定时清理日志导入时产生的临时文件

	$RCSfile: cleanimporttemp.php,v $
	$Revision: 1.2 $
	$Date: 2006/04/25 08:06:00 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$filepath = S_ROOT.'./data/';
$filename = '';
$filearr = sreaddir($filepath);
foreach ($filearr as $tempfile) {
	$filename = $filepath.$tempfile;
	if(substr($tempfile, 0, 11) == 'blogimport_') {
		if ($_SGLOBAL['timestamp'] - filemtime($filename) > 600 ) {
			if(!@unlink($filename)) {
				errorlog('Cron', srealpath($tempfile).' Not found or have no access!', 0);
			}
		}
	} 
}
?>