<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	论坛缓存更新

	$RCSfile: updatebbscache.php,v $
	$Revision: 1.2 $
	$Date: 2006/09/21 08:42:27 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/cache.func.php');

//缓存论坛设置
updatebbssetting();
//缓存论坛风格设置
updatebbsstyle();
//缓存论坛语言屏蔽
updatebbscensor();
//缓存论坛bbcode/smiles
updatebbsbbcode();

?>