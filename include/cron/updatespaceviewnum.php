<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	个人空间查看数更新

	$RCSfile: updatespaceviewnum.php,v $
	$Revision: 1.4 $
	$Date: 2007/03/08 20:11:48 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$logfile = S_ROOT.'./log/spaceviewcount.log';

if(@$viewlog = file($logfile)) {
	if(@$fp = fopen($logfile, 'w')) {
		fwrite($fp, "0\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		@unlink($logfile);
	}

	$uidarray = $viewarray = array();
	foreach($viewlog as $uid) {
		$uid = intval($uid);
		if($uid) {
			if(empty($uidarray[$uid])) $uidarray[$uid] = 0;
			$uidarray[$uid]++;
		}
	}
	
	foreach($uidarray as $uid => $views) {
		if(empty($viewarray[$views])) {
			$viewarray[$views] = '';
			$comma = '';
		}
		$viewarray[$views] .= $comma.$uid;
		$comma = ',';
	}

	foreach($viewarray as $views => $uids) {
		$_SGLOBAL['db']->query('UPDATE '.tname('userspaces').' SET viewnum=viewnum+'.$views.' WHERE uid IN ('.$uids.')');
	}
}


?>