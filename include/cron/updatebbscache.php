<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	��̳�������

	$RCSfile: updatebbscache.php,v $
	$Revision: 1.2 $
	$Date: 2006/09/21 08:42:27 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./function/cache.func.php');

//������̳����
updatebbssetting();
//������̳�������
updatebbsstyle();
//������̳��������
updatebbscensor();
//������̳bbcode/smiles
updatebbsbbcode();

?>