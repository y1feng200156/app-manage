<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：处理圈内推荐信息

	$RCSfile: block_recommend_query.inc.php,v $
	$Revision: 1.7 $
	$Date: 2007/04/06 22:07:32 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	$sql = array();
	
	$sql['select'] = 'SELECT g.gid, g.itemid, g.uid AS guid, g.username AS gusername, g.dateline AS gdateline, i.* ';
	$sql['from'] = 'FROM '.tname('groupitems').' g INNER JOIN '.tname('spaceitems').' i ON i.itemid = g.itemid AND i.folder=1 ';

	if(!empty($paramarr['gid'])) {
		$paramarr['gid'] = intval($paramarr['gid']);
		if($paramarr['gid']) $wherearr[] = 'g.gid = \''.$paramarr['gid'].'\'';
	}
	if(!empty($paramarr['itemid'])) {
		$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
		if($paramarr['itemid']) $wherearr[] = 'g.itemid IN ('.$paramarr['itemid'].')';
	}
	if(!empty($paramarr['dateline'])) {
		$paramarr['dateline'] = intval($paramarr['dateline']);
		if($paramarr['dateline']) $wherearr[] = 'g.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
	}

	if(!empty($wherearr)) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
	
	//order
	if(!empty($paramarr['order'])) {
		$sql['order'] = 'ORDER BY '.$paramarr['order'];
	}
	
	//limit
	if(!empty($paramarr['perpage'])) {
		$paramarr['perpage'] = intval($paramarr['perpage']);
		if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

		if(empty($_SGET['page'])) $_SGET['page'] = 1;
		$_SGET['page'] = intval($_SGET['page']);
		if($_SGET['page'] < 1) $_SGET['page'] = 1;

		$start = ($_SGET['page']-1)*$paramarr['perpage'];
		$sql['limit'] = 'LIMIT '.$start.','.$paramarr['perpage'];
	} else {
		if(empty($paramarr['limit'])) {
			$sql['limit'] = 'LIMIT 0,1';
		} else {
			$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
			if($paramarr['limit']) {
				$sql['limit'] = 'LIMIT '.$paramarr['limit'];
			} else {
				$sql['limit'] = 'LIMIT 0,1';
			}
		}
	}
	
	$sqlstring = implode(' ', $sql);

} else {
	$sqlstring = getblocksql($paramarr['sql']);
	unset($paramarr['perpage']);
}

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('groupitems').' g '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;

	//查询
	$query = $_SGLOBAL['db']->query($sqlstring);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {

		//标题处理
		$value['subjectall'] = $value['subject'];
		if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
			$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}

		//类型
		if(!empty($value['type'])) $value['typename'] = $lang[$value['type']];

		//链接
		$value['url'] = geturl('uid/'.$value['uid'].'/action/viewspace/itemid/'.$value['itemid']);

		$theblockarr[] = $value;
	}
}

?>
