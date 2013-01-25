<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	¸½¼þÇåÀí

	$RCSfile: cleanattachment.php,v $
	$Revision: 1.7 $
	$Date: 2006/12/01 07:39:12 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE isavailable=0 AND dateline<'.($_SGLOBAL['timestamp']-3600*24));

$delaidarr = array();
$delsizearr = array();

while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
	
	$deletefileflag = true;
	if(!empty($attach['filepath'])) {
		$attachfilepath = A_DIR.'/'.$attach['filepath'];
		if(file_exists($attachfilepath)) {
			if(!@unlink($attachfilepath)) {
				$deletefileflag = false;
				errorlog('Cron', srealpath($attachfilepath).' have no permission to be removed.', 0);
			}
		} else {
			errorlog('Cron', srealpath($attachfilepath).' not found.', 0);
		}
	}
	
	$deletethumbflag = true;
	if(!empty($attach['thumbpath'])) {
		$attachthumbpath = A_DIR.'/'.$attach['thumbpath'];
		if(file_exists($attachthumbpath)) {
			if(!@unlink($attachthumbpath)) {
				$deletethumbflag = false;
				errorlog('Cron', srealpath($attachthumbpath).' have no permission to be removed.', 0);
			}
		} else {
			errorlog('Cron', srealpath($attachthumbpath).' not found.', 0);
		}
	}
	
	if($deletefileflag && $deletethumbflag) {
		$uid = $attach['uid'];
		$delaidarr[] = $attach['aid'];
		if(empty($delsizearr[$uid])) {
			$delsizearr[$uid] = $attach['size'];
		} else {
			$delsizearr[$uid] = $delsizearr[$uid] + $attach['size'];
		}
	}
}

if(!empty($delaidarr)) {
	$_SGLOBAL['db']->query('DELETE FROM '.tname('attachments').' WHERE aid IN ('.simplode($delaidarr).')');
}

if(!empty($delsizearr)) {
	foreach ($delsizearr as $uid => $attachsize) {
		$_SGLOBAL['db']->query('UPDATE '.tname('userspacefields').' SET attachsize=attachsize-'.$attachsize.' WHERE uid=\''.$uid.'\'');
	}
}

?>