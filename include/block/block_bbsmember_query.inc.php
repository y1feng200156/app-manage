<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛用户

	$RCSfile: block_bbsmember_query.inc.php,v $
	$Revision: 1.13.10.1 $
	$Date: 2007/05/29 18:51:42 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	
	//set sql var
	$sql = array();
	
	//select
	$sql['select'] = 'SELECT m.*';
	
	//from
	$sql['from'] = 'FROM '.tname('members', 1).' m';
	
	//join
	if(!empty($paramarr['showdetail'])) {
		$sql['select'] = 'SELECT mm.*, m.*';
		$sql['join'] = 'LEFT JOIN '.tname('memberfields', 1).' mm ON mm.uid = m.uid';
	}

	//where
	$wherearr = array();
	if(!empty($paramarr['uid'])) {
		$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
		if($paramarr['uid']) $wherearr[] = 'm.uid IN ('.$paramarr['uid'].')';
	} else {
		if(!empty($paramarr['adminid'])) {
			$paramarr['adminid'] = getdotstring($paramarr['adminid'], 'int');
			if($paramarr['adminid']) $wherearr[] = 'm.adminid IN ('.$paramarr['adminid'].')';
		}
		if(!empty($paramarr['groupid'])) {
			$paramarr['groupid'] = getdotstring($paramarr['groupid'], 'int');
			if($paramarr['groupid']) $wherearr[] = 'm.groupid IN ('.$paramarr['groupid'].')';
		}
		if(!empty($paramarr['regdate'])) {
			$paramarr['regdate'] = intval($paramarr['regdate']);
			if($paramarr['regdate']) $wherearr[] = 'm.regdate >= '.($_SGLOBAL['timestamp']-$paramarr['regdate']);
		}
		if(!empty($paramarr['lastvisit'])) {
			$paramarr['lastvisit'] = intval($paramarr['lastvisit']);
			if($paramarr['lastvisit']) $wherearr[] = 'm.lastvisit >= '.($_SGLOBAL['timestamp']-$paramarr['lastvisit']);
		}
		if(!empty($paramarr['lastpost'])) {
			$paramarr['lastpost'] = intval($paramarr['lastpost']);
			if($paramarr['lastpost']) $wherearr[] = 'm.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
		}
		$scopequery = getscopequery('m', 'posts', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'digestposts', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'oltime', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'pageviews', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits1', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits2', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits3', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits4', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits5', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits6', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits7', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		$scopequery = getscopequery('m', 'credits8', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
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
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('members', 1).' m '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	if(empty($paramarr['signaturedot'])) $paramarr['signaturedot'] = 0;
	
	//查询
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		//签名档
		if(!empty($member['signature']) && !empty($paramarr['signaturelen'])) {
			$value['signature'] = cutstr($value['signature'], $paramarr['signaturelen'], $paramarr['signaturedot']);
		}
		
		//头像
		if(!empty($value['avatar'])) {
			$check_url = parse_url($value['avatar']);
			if(empty($check_url['scheme'])) {
				$value['avatar'] = B_URL.'/'.$value['avatar'];
			}
		} else {
			$value['avatar'] = S_URL.'/images/base/space_noface.gif';
		}
		
		//链接
		$value['url'] = geturl("uid/$value[uid]");
		
		$theblockarr[] = $value;
	}
}

?>