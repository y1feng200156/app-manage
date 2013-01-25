<?php
/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：groupuser

	$RCSfile: block_groupuser_query.inc.php,v $
	$Revision: 1.4 $
	$Date: 2007/04/06 19:12:53 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	$sql = array();
	$sql['select'] = 'SELECT g.*, u.*';
	$sql['from'] = 'FROM '.tname('groupuid').' g';
	$sql['join'] = ' LEFT JOIN '.tname('userspaces').' u ON u.uid = g.uid';
	
	$wherearr = array();
	
	if(!empty($paramarr['gid'])) {
		$paramarr['gid'] = getdotstring($paramarr['gid'], 'int');
		if($paramarr['gid']) $wherearr[] = 'g.gid IN ('.$paramarr['gid'].')';
	}
	if(!empty($paramarr['flag'])) {
		$paramarr['flag'] = intval($paramarr['flag']);
		if($paramarr['flag']) $wherearr[] = 'g.flag=\''.$paramarr['flag'].'\'';
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
	//query
	$sqlstring = implode(' ', $sql);
} else {
	$sqlstring = getblocksql($paramarr['sql']);
	unset($paramarr['perpage']);
}

$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) '.$sql['from'].' '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	//查询
	$query = $_SGLOBAL['db']->query($sqlstring);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {

		//用户头像
		$value['photo'] = getphoto($value['uid'], $value['photo']);
		
		//链接
		$value['url'] = geturl('uid/'.$value['uid']);
		
		$theblockarr[] = $value;
	}
}

?>