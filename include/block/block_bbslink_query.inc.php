<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛链接

	$RCSfile: block_bbslink_query.inc.php,v $
	$Revision: 1.9 $
	$Date: 2007/05/11 20:35:31 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	
	//set sql var
	$sql = array();
	
	//select
	$sql['select'] = 'SELECT *';
	
	//from
	$sql['from'] = 'FROM '.tname('forumlinks', 1);
	
	//where
	$wherearr = array();
	if(!empty($paramarr['id'])) {
		$paramarr['id'] = getdotstring($paramarr['id'], 'int');
		if($paramarr['id']) $wherearr[] = 'id IN ('.$paramarr['id'].')';
	} else {
		if(!empty($paramarr['note'])) {
			$wherearr[] = 'description != \'\'';
		}
		if(!empty($paramarr['logo'])) {
			$wherearr[] = 'logo != \'\'';
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
	$sqlstring = implode(' ',$sql);
} else {
	$sqlstring = getblocksql($paramarr['sql']);
	unset($paramarr['perpage']);
}

dbconnect(1);

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('forumlinks', 1).' '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	if(empty($paramarr['namedot'])) $paramarr['namedot'] = 0;
	if(empty($paramarr['notedot'])) $paramarr['notedot'] = 0;
	
	//查询
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		//链接名
		$value['nameall'] = $value['name'];
		if(!empty($value['name']) && !empty($paramarr['namelen'])) {
			$value['name'] = cutstr($value['name'], $paramarr['namelen'], $paramarr['namedot']);
		}
		
		//注释
		$value['noteall'] = $value['description'];
		if(!empty($value['description']) && !empty($paramarr['notelen'])) {
			$value['note'] = $value['description'] = cutstr($value['description'], $paramarr['notelen'], $paramarr['notedot']);
		}		

		//logo
		if(!empty($value['logo'])) {
			$check_url = parse_url($value['logo']);
			if(empty($check_url['scheme'])) {
				$value['logo'] = B_URL.'/'.$value['logo'];
			}
		}

		$theblockarr[] = $value;
	}
}

?>