<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	广告点击数更新

	$RCSfile: updateadclicks.php,v $
	$Revision: 1.4 $
	$Date: 2007/03/08 20:11:48 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$logfile = S_ROOT.'./log/adclickscount.log';

if(@$clicklog = file($logfile)) {

	if(@$fp = fopen($logfile, 'w')) {
		fwrite($fp, "0\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		@unlink($logfile);
	}

	$adidarray = $clickarray = array();
	foreach($clicklog as $adid) {
		$adid = intval($adid);
		if($adid) {
			if(empty($adidarray[$adid])) $adidarray[$adid] = 0;
			$adidarray[$adid]++;
		}
	}
	
	foreach($adidarray as $adid => $clicks) {
		if(empty($clickarray[$clicks])) {
			$clickarray[$clicks] = '';
			$comma = '';
		}
		$clickarray[$clicks] .= $comma.$adid;
		$comma = ',';
	}

	foreach($clickarray as $clicks => $adids) {
		$_SGLOBAL['db']->query('UPDATE '.tname('ads').' SET clicks=clicks+'.$clicks.' WHERE adid IN ('.$adids.')');
	}
}


?>