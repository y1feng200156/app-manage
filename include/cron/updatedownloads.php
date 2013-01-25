<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	����������

	$RCSfile: updatedownloads.php,v $
	$Revision: 1.4 $
	$Date: 2007/03/08 20:11:48 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$logfile = S_ROOT.'./log/downloadcount.log';

if(@$viewlog = file($logfile)) {
	if(@$fp = fopen($logfile, 'w')) {
		fwrite($fp, "0\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		@unlink($logfile);
	}

	$aidarray = $viewarray = array();
	foreach($viewlog as $aid) {
		$aid = intval($aid);
		if($aid) {
			if(empty($aidarray[$aid])) $aidarray[$aid] = 0;
			$aidarray[$aid]++;
		}
	}
	
	foreach($aidarray as $aid => $views) {
		if(empty($viewarray[$views])) {
			$viewarray[$views] = '';
			$comma = '';
		}
		$viewarray[$views] .= $comma.$aid;
		$comma = ',';
	}

	foreach($viewarray as $views => $aids) {
		$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET downloads=downloads+'.$views.' WHERE aid IN ('.$aids.')');
	}
}


?>