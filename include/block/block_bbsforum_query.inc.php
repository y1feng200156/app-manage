<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛版块

	$RCSfile: block_bbsforum_query.inc.php,v $
	$Revision: 1.16 $
	$Date: 2007/04/06 19:16:23 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	
	//set sql var
	$sql = array();
	
	//select
	$sql['select'] = 'SELECT f.*';
	
	//from
	$sql['from'] = 'FROM '.tname('forums', 1).' f';
	
	//join
	if(!empty($paramarr['showdetail'])) {
		$sql['select'] = 'SELECT ff,*, f.*';
		$sql['join'] = 'LEFT JOIN '.tname('forumfields', 1).' ff ON ff.fid = f.fid';
	}

	//where
	$wherearr = array();

	$wherearr[] = (empty($paramarr['status'])?'f.status>0':'f.status='.$paramarr['status']);

	if(!empty($paramarr['fid'])) {
		$paramarr['fid'] = getdotstring($paramarr['fid'], 'int');
		if($paramarr['fid']) $wherearr[] = 'f.fid IN ('.$paramarr['fid'].')';
	}
	
	if(!empty($paramarr['fup'])) {
		$paramarr['fup'] = getdotstring($paramarr['fup'], 'int', true);
		if($paramarr['fup']) $wherearr[] = 'f.fup IN ('.$paramarr['fup'].')';
	}
	
	if(!empty($paramarr['type'])) {
		$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, array('group', 'forum', 'sub'));
		if($paramarr['type']) $wherearr[] = 'f.type IN ('.$paramarr['type'].')';
	}

	$scopequery = getscopequery('f', 'threads', $paramarr);
	if(!empty($scopequery)) $wherearr[] = $scopequery;
	
	$scopequery = getscopequery('f', 'posts', $paramarr);
	if(!empty($scopequery)) $wherearr[] = $scopequery;
	
	$scopequery = getscopequery('f', 'todayposts', $paramarr);
	if(!empty($scopequery)) $wherearr[] = $scopequery;

	if(!empty($paramarr['allowblog'])) {
		if(B_VER == '5') {
			$wherearr[] = 'f.allowshare = 1';
		} elseif(B_VER == '4') {
			$wherearr[] = 'f.allowblog = 1';
		}
	}
	
	if(!empty($paramarr['allowtrade'])) {
		if(B_VER == '5') {
			$wherearr[] = 'f.allowpostspecial = 1';
		} elseif(B_VER == '4') {
			$wherearr[] = 'f.allowtrade = 1';
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
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('forums', 1).' f '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	@include_once(S_ROOT.'./data/system/bbsforums.cache.php');
	
	//查询
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		//版块名
		if(!empty($_SGLOBAL['bbsforumarr']) && !empty($_SGLOBAL['bbsforumarr'][$value['fid']]['name'])) {
			$value['name'] = $_SGLOBAL['bbsforumarr'][$value['fid']]['name'];
		}

		//链接
		if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
			$value['url'] = B_URL.'/forumdisplay.php?fid='.$value['fid'];
		} else {
			$value['url'] = geturl('action/forumdisplay/fid/'.$value['fid']);
		}
		
		$theblockarr[] = $value;
	}
}

?>