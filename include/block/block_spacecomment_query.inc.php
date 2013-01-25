<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：评论

	$RCSfile: block_spacecomment_query.inc.php,v $
	$Revision: 1.9 $
	$Date: 2007/04/06 19:12:53 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	$sql = array();
	$sql['select'] = 'SELECT *';
	$sql['from'] = 'FROM '.tname('spacecomments');

	$wherearr = array();
	//where
	if(!empty($paramarr['cid'])) {
		$paramarr['cid'] = getdotstring($paramarr['cid'], 'int');
		if($paramarr['cid']) $wherearr[] = 'cid IN ('.$paramarr['cid'].')';
	} else {
		if(!empty($paramarr['itemid'])) {
			$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
			if($paramarr['itemid']) $wherearr[] = 'itemid IN ('.$paramarr['itemid'].')';
		}
		if(!empty($paramarr['uid'])) {
			$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
			if($paramarr['uid']) $wherearr[] = 'uid IN ('.$paramarr['uid'].')';
		}
		if(!empty($paramarr['authorid'])) {
			$paramarr['authorid'] = getdotstring($paramarr['authorid'], 'int');
			if($paramarr['authorid']) $wherearr[] = 'authorid IN ('.$paramarr['authorid'].')';
		}
		if(!empty($paramarr['type'])) {
			$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, $_SGLOBAL['type'], 1);
			if($paramarr['type']) $wherearr[] = 'type IN ('.$paramarr['type'].')';
		}
	}
	$sql['where'] = '';
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

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacecomments').' '.$sql['where']), 0);
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
		if($value['type'] == 'news') {
			$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
		} else {
			$value['url'] = geturl('uid/'.$value['uid'].'/action/viewspace/itemid/'.$value['itemid']);
		}
		
		$theblockarr[] = $value;
	}
}

?>