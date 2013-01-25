<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛附件

	$RCSfile: block_bbsattachment_query.inc.php,v $
	$Revision: 1.15.6.3 $
	$Date: 2007/05/29 18:51:42 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	
	$wherearr = array();
	$wherearr[] = 't.tid = a.tid';
	if(empty($paramarr['aid'])) {
		if(B_VER == '5') {
			$wherearr[] = 't.supe_pushstatus > 0';
		}
		if(!empty($paramarr['t_fid'])) {
			$paramarr['t_fid'] = getdotstring($paramarr['t_fid'], 'int');
			if($paramarr['t_fid']) $wherearr[] = 't.fid IN ('.$paramarr['t_fid'].')';
		}
	
		if(!empty($paramarr['t_typeid'])) {
			$paramarr['t_typeid'] = getdotstring($paramarr['t_typeid'], 'int');
			if($paramarr['t_typeid']) $wherearr[] = 't.typeid IN ('.$paramarr['t_typeid'].')';
		}
	
		if(!empty($paramarr['t_authorid'])) {
			$paramarr['t_authorid'] = getdotstring($paramarr['t_authorid'], 'int');
			if($paramarr['t_authorid']) $wherearr[] = 't.authorid IN ('.$paramarr['t_authorid'].')';
		}
	
		if(!empty($paramarr['t_digest'])) {
			$paramarr['t_digest'] = getdotstring($paramarr['t_digest'], 'int');
			if($paramarr['t_digest']) $wherearr[] = 't.digest IN ('.$paramarr['t_digest'].')';
		}
		
		if(!empty($paramarr['t_dateline'])) {
			$paramarr['t_dateline'] = intval($paramarr['t_dateline']);
			if($paramarr['t_dateline']) $wherearr[] = 't.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['t_dateline']);
		}
		if(!empty($paramarr['t_lastpost'])) {
			$paramarr['t_lastpost'] = intval($paramarr['t_lastpost']);
			if($paramarr['t_lastpost']) $wherearr[] = 't.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['t_lastpost']);
		}
	
		if(!empty($paramarr['t_readperm'])) $paramarr['readperm'] = $paramarr['t_readperm'];
		if(!empty($paramarr['t_price'])) $paramarr['price'] = $paramarr['t_price'];
		if(!empty($paramarr['t_views'])) $paramarr['views'] = $paramarr['t_views'];
		if(!empty($paramarr['t_replies'])) $paramarr['replies'] = $paramarr['t_replies'];
		if(!empty($paramarr['t_rate'])) $paramarr['rate'] = $paramarr['t_rate'];
		
		$scopequery = getscopequery('t', 'dateline', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'lastpost', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('t', 'readperm', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
	
		$scopequery = getscopequery('t', 'price', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
	
		$scopequery = getscopequery('t', 'views', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
	
		$scopequery = getscopequery('t', 'replies', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
	
		$scopequery = getscopequery('t', 'rate', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
	
		if(!empty($paramarr['t_blog'])) {
			$wherearr[] = 't.blog = 1';
		}
		
		if(!empty($paramarr['t_closed'])) {
			$wherearr[] = 't.closed = 1';
		}
		$wherearr[] = 't.displayorder >= 0';
	}
	$innersql = implode(' AND ', $wherearr);

	//set sql var
	$sql = array();
	
	//select
	$sql['select'] = 'SELECT t.*, a.*, a.dateline AS a_dateline, a.readperm AS a_readperm, a.attachment AS a_attachment';
	
	//from
	$sql['from'] = 'FROM '.tname('attachments', 1).' a';

	//join
	$sql['join'] = 'INNER JOIN '.tname('threads', 1).' t ON '.$innersql;

	//where
	$wherearr = array();
	if(!empty($paramarr['aid'])) {
		$paramarr['aid'] = getdotstring($paramarr['aid'], 'int');
		if($paramarr['aid']) $wherearr[] = 'a.aid IN ('.$paramarr['aid'].')';
	} else {
		if(!empty($paramarr['filetype'])) {
			$paramarr['filetype'] = getdotstring($paramarr['filetype'], 'char', false, array('file', 'image'));
			
			if(B_VER == '5') {
				if($paramarr['filetype'] == '\'image\'') {
					$wherearr[] = 'a.isimage = 1';
				} elseif($paramarr['filetype'] == '\'file\'') {
					$wherearr[] = 'a.isimage = 0';
				}
			} elseif(B_VER == '4') {
				$imagestr = '\'image/bmp\', \'image/gif\', \'image/jpeg\', \'image/pjpeg\', \'image/png\'';
				if($paramarr['filetype'] == '\'image\'') {
					$wherearr[] = 'a.filetype IN ('.$imagestr.')';
				} elseif($paramarr['filetype'] == '\'file\'') {
					$wherearr[] = 'NOT(a.filetype IN ('.$imagestr.'))';
				}
			}
		}
		$scopequery = getscopequery('a', 'dateline', $paramarr, 1);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('a', 'readperm', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('a', 'downloads', $paramarr);
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
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('attachments', 1).' a INNER JOIN '.tname('threads', 1).' t ON '.$innersql.' '.$sql['where']), 0);
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
	
	//查询
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		
		//帖子标题处理
		$value['subjectall'] = $value['subject'];
		if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
			$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}
		
		//附件路径
		$value['a_attachment'] = $value['attachment'] = $value['a_thumbfile'] = getbbsattachment($value);

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