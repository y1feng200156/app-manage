<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛公告

	$RCSfile: block_bbsannouncement_query.inc.php,v $
	$Revision: 1.9 $
	$Date: 2007/04/06 19:12:53 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	//set sql var
	$sql = array();
	
	//select
	$sql['select'] = 'SELECT id, author, subject, starttime, endtime';
	if(!empty($paramarr['showdetail'])) {
		$sql['select'] .= ', message';
	}
	
	//from
	$sql['from'] = 'FROM '.tname('announcements', 1);
	
	//where
	$wherearr = array();
	if(!empty($paramarr['aid'])) {
		$paramarr['aid'] = getdotstring($paramarr['aid'], 'int');
		if($paramarr['aid']) $wherearr[] = 'id IN ('.$paramarr['aid'].')';
	} else {
		$wherearr[] = 'starttime <= '.$_SGLOBAL['timestamp'].' AND (endtime =0 OR endtime >= '.$_SGLOBAL['timestamp'].')';
		if(!empty($paramarr['author'])) {
			$paramarr['author'] = getdotstring($paramarr['author'], 'char');
			if($paramarr['author']) $wherearr[] = 'author IN ('.$paramarr['author'].')';
		}
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
	$sqlstring = implode(' ',$sql);
} else {
	$sqlstring = getblocksql($paramarr['sql']);
	unset($paramarr['perpage']);
}

dbconnect(1);

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('announcements', 1).' '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//变量处理
	if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
	if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
	
	//查询
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		//标题处理
		$value['subjectall'] = $value['subject'];
		if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
			$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}
		
		//内容处理
		if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
			$value['message'] = trim(strip_tags($value['message']));
			$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
		}
		
		//链接
		$value['url'] = B_URL.'/announcement.php?id='.$value['id'];
		
		$theblockarr[] = $value;
	}
}

?>