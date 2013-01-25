<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	热点TAG生成

	$RCSfile: tagcontent.php,v $
	$Revision: 1.3 $
	$Date: 2006/10/31 04:58:44 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$cachefile = S_ROOT.'./data/system/tag.cache.php';

$tagnamearr = array();
$query = $_SGLOBAL['db']->query('SELECT tagname FROM '.tname('tags').' ORDER BY spaceallnum DESC LIMIT 0,100');
while ($tag = $_SGLOBAL['db']->fetch_array($query)) {
	if(strlen($tag['tagname'])>2) $tagnamearr[] = $tag['tagname'];
}

if(empty($tagnamearr)) {
	$text = '';
} else {
	$text = '$_SGLOBAL[\'tagcontent\']=\''.implode('|', $tagnamearr).'\';';
}

if(!writefile($cachefile, $text, 'php', 'w', 0)) {
	errorlog('Cron', srealpath($cachefile).' Not found or have no access!', 0);
}


?>