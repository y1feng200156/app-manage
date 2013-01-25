<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛主题

	$RCSfile: block_bbsthread_query.inc.php,v $
	$Revision: 1.24 $
	$Date: 2007/04/16 19:06:18 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	//set sql var
	$sql = array();
	
	//select
	$sql['select'] = 'SELECT t.*';
	
	//from
	$sql['from'] = 'FROM '.tname('threads', 1).' t';
	
	if(!empty($paramarr['showdetail'])) {
		$sql['select'] .= ', p.message, p.htmlon, p.bbcodeoff, p.smileyoff, p.parseurloff';
		$sql['join'] = 'LEFT JOIN '.tname('posts', 1).' p ON p.tid=t.tid AND p.first=1';
	}
	
	//where
	$wherearr = array();
	if(!empty($paramarr['tid'])) {
		$paramarr['tid'] = getdotstring($paramarr['tid'], 'int');
		if($paramarr['tid']) $wherearr[] = 't.tid IN ('.$paramarr['tid'].')';
	} else {

		if(!empty($paramarr['blog'])) {
			$wherearr[] = 't.blog = 1';
		}
		
		if(!empty($paramarr['fid'])) {
			$paramarr['fid'] = getdotstring($paramarr['fid'], 'int');
			if($paramarr['fid']) $wherearr[] = 't.fid IN ('.$paramarr['fid'].')';
		}
		
		if(!empty($paramarr['sgid'])) {
			$paramarr['sgid'] = getdotstring($paramarr['sgid'], 'int');
			if($paramarr['sgid']) $wherearr[] = 't.sgid IN ('.$paramarr['sgid'].')';
		}

		if(!empty($paramarr['typeid'])) {
			$paramarr['typeid'] = getdotstring($paramarr['typeid'], 'int');
			if($paramarr['typeid']) $wherearr[] = 't.typeid IN ('.$paramarr['typeid'].')';
		}

		if(!empty($paramarr['authorid'])) {
			$paramarr['authorid'] = getdotstring($paramarr['authorid'], 'int');
			if($paramarr['authorid']) $wherearr[] = 't.authorid IN ('.$paramarr['authorid'].')';
		}

		if(!empty($paramarr['digest'])) {
			$paramarr['digest'] = getdotstring($paramarr['digest'], 'int');
			if($paramarr['digest']) $wherearr[] = 't.digest IN ('.$paramarr['digest'].')';
		}
		
		$scopequery = getscopequery('t', 'readperm', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'price', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'views', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'dateline', $paramarr, 1);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'lastpost', $paramarr, 1);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'replies', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'rate', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		if(!empty($paramarr['poll'])) {
			if(B_VER == '5') {
				$wherearr[] = 't.special = 1';
			} elseif(B_VER == '4') {
				$wherearr[] = 't.poll = 1';
			}
		}
		if(!empty($paramarr['attachment'])) {
			$wherearr[] = 't.attachment = 1';
		}
		if(!empty($paramarr['closed'])) {
			$wherearr[] = 't.closed = 1';
		}
		$wherearr[] = 't.displayorder >= 0';
		if(empty($paramarr['sgid'])) {
			if(B_VER == '5') {
				$wherearr[] = 't.supe_pushstatus > 0';//版本5推送
			}
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
	$sqlstring = implode(' ', $sql);
} else {
	$sqlstring = getblocksql($paramarr['sql']);
	unset($paramarr['perpage']);
}

dbconnect(1);

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('threads', 1).' t '.$sql['where']), 0);
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
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		//标题处理
		$value['subjectall'] = $value['subject'];
		if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
			$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}
		
		//内容处理
		if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
			$value['message'] = trim(strip_tags(stripbbcode($value['message'])));
			$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
		}

		//链接
		if($_SCONFIG['bbsurltype'] == 'bbs' || (!empty($paramarr['bbsurltype']) && $paramarr['bbsurltype'] == 'bbs')) {
			$value['url'] = B_URL.'/viewthread.php?tid='.$value['tid'];
		} else {
			$value['url'] = geturl('action/viewthread/tid/'.$value['tid']);
		}
		
		$theblockarr[] = $value;
	}
}

?>