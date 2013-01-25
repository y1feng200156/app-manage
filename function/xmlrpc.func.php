<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	XML½Ó¿Ú

	$RCSfile: xmlrpc.func.php,v $
	$Revision: 1.5 $
	$Date: 2007/05/10 15:06:34 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function xml_usetype($type) {
	global $_SCONFIG;
	if(empty($_SCONFIG['channel'][$type])) {
		return false;
	} else {
		return true;
	}
}

function xml_checktypeauth($type) {
	global $_SGLOBAL;
	if(!xml_usetype($type)) {
		return false;
	} elseif (empty($_SGLOBAL['group']['allowspace'.$type])) {
		return false;
	} else {
		return true;
	}
}

function xml_checkitemuser($itemid, $member) {
	global $_SGLOBAL;
	$itemid = intval($itemid);
	if(!empty($itemid)) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid='$itemid' AND uid='".$member['uid']."'");
		if(!$_SGLOBAL['theitem'] = $_SGLOBAL['db']->fetch_array($query)) {
			return false;
		} else {
			return true;
		}
	}
}

function checkhtml($html) {
	$html = preg_replace("/(\<|\s+)o([a-z]+\s?=)/is", "\\1\\2", $html);
	$html = preg_replace("/(script|frame|form|meta|behavior)([\s|:|>])+/i", "\\1.\\2", $html);
	//$html = str_replace('&#', '&amp;#', $html);
	return $html;
}
?>