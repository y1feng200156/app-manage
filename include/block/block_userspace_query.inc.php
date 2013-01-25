<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：用户个人空间

	$RCSfile: block_userspace_query.inc.php,v $
	$Revision: 1.10 $
	$Date: 2007/04/06 19:12:53 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	$sql = array();
	$sql['select'] = 'SELECT u.*';
	$sql['from'] = 'FROM '.tname('userspaces').' u';
	
	if(!empty($paramarr['showdetail'])) {
		$sql['select'] = 'SELECT uu.*, u.*';
		$sql['from'] .= ' LEFT JOIN '.tname('userspacefields').' uu ON uu.uid=u.uid';
	}

	$wherearr = array();
	//where
	if(!empty($paramarr['uid'])) {
		$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
		if($paramarr['uid']) $wherearr[] = 'u.uid IN ('.$paramarr['uid'].')';
	} else {
		if(!empty($paramarr['catid'])) {
			$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
			if($paramarr['catid']) $wherearr[] = 'u.catid IN ('.$paramarr['catid'].')';
		}
		if(!empty($paramarr['dateline'])) {
			$paramarr['dateline'] = intval($paramarr['dateline']);
			if($paramarr['dateline']) $wherearr[] = 'u.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
		}
		if(!empty($paramarr['lastpost'])) {
			$paramarr['lastpost'] = intval($paramarr['lastpost']);
			if($paramarr['lastpost']) $wherearr[] = 'u.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
		}
		
		$scopequery = getscopequery('u', 'spaceallnum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('u', 'spaceblognum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('u', 'spaceimagenum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('u', 'spacefilenum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('u', 'spacegoodsnum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('u', 'spacelinknum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		$scopequery = getscopequery('u', 'spacevideonum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		if(!empty($paramarr['isstar'])) {
			if($paramarr['isstar'] == 1) {
				$wherearr[] = 'u.isstar = 1';
			} else {
				$wherearr[] = 'u.isstar = 0';
			}
		}
		$wherearr[] = 'u.islock = 0';
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

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('userspaces').' u '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	if(!empty($paramarr['showcategory'])) {
		include_once(S_ROOT.'./data/system/category.cache.php');
	}
	
	//查询
	$query = $_SGLOBAL['db']->query($sqlstring);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {

		//链接
		$value['url'] = geturl('uid/'.$value['uid']);
		
		//用户头像
		$value['photo'] = getphoto($value['uid'], $value['photo']);

		//分类名
		if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
		
		$theblockarr[] = $value;
	}
}

?>