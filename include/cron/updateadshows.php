<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	广告显示数更新

	$RCSfile: updateadshows.php,v $
	$Revision: 1.3 $
	$Date: 2006/10/27 05:16:33 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//$logfile = S_ROOT.'./log/adshowscount.log';
//
//if(@$showlog = file($logfile)) {
//	@unlink($logfile);
//
//	$adidarray = $showarray = array();
//	foreach($showlog as $adid) {
//		$adid = intval($adid);
//		if($adid) {
//			if(empty($adidarray[$adid])) $adidarray[$adid] = 0;
//			$adidarray[$adid]++;
//		}
//	}
//	
//	foreach($adidarray as $adid => $shows) {
//		if(empty($showarray[$shows])) {
//			$showarray[$shows] = '';
//			$comma = '';
//		}
//		$showarray[$shows] .= $comma.$adid;
//		$comma = ',';
//	}
//
//	foreach($showarray as $shows => $adids) {
//		$_SGLOBAL['db']->query('UPDATE '.tname('ads').' SET shows=shows+'.$shows.' WHERE adid IN ('.$adids.')');
//	}
//}


?>