<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	信息查看数更新

	$RCSfile: updateviewnum.php,v $
	$Revision: 1.4 $
	$Date: 2007/03/08 20:11:48 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$logfile = S_ROOT.'./log/viewcount.log';

if(@$viewlog = file($logfile)) {
	if(@$fp = fopen($logfile, 'w')) {
		fwrite($fp, "0\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		@unlink($logfile);
	}

	$itemidarray = $viewarray = array();
	foreach($viewlog as $itemid) {
		$itemid = intval($itemid);
		if($itemid) {
			if(empty($itemidarray[$itemid])) $itemidarray[$itemid] = 0;
			$itemidarray[$itemid]++;
		}
	}
	
	foreach($itemidarray as $itemid => $views) {
		if(empty($viewarray[$views])) {
			$viewarray[$views] = '';
			$comma = '';
		}
		$viewarray[$views] .= $comma.$itemid;
		$comma = ',';
	}

	foreach($viewarray as $views => $itemids) {
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET viewnum=viewnum+'.$views.' WHERE itemid IN ('.$itemids.')');
	}
}


?>