<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：公告

	$RCSfile: block_announcement_query.inc.php,v $
	$Revision: 1.5 $
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
	$sql['from'] = 'FROM '.tname('announcements');

	//where
	$wherearr = array();
	if(!empty($paramarr['id'])) {
		$paramarr['id'] = getdotstring($paramarr['id'], 'int');
		if($paramarr['id']) $wherearr[] = 'id IN ('.$paramarr['id'].')';
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

//multi
$listcount = 1;//默认读取
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('announcements').' '.$sql['where']), 0);
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
	if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
	
	//查询
	$query = $_SGLOBAL['db']->query($sqlstring);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		
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
		$value['url'] = geturl('action/announcement/id/'.$value['id']);
		
		$theblockarr[] = $value;
	}
}

?>