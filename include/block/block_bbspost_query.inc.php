<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：论坛帖子

	$RCSfile: block_bbspost_query.inc.php,v $
	$Revision: 1.8 $
	$Date: 2007/04/06 19:05:57 $
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
	$sql['from'] = 'FROM '.tname('posts', 1);
	
	//where
	$wherearr = array();
	if(!empty($paramarr['pid'])) {
		$paramarr['pid'] = getdotstring($paramarr['pid'], 'int');
		$wherearr[] = 'pid IN ('.$paramarr['pid'].')';
	} else {
		$wherearr[] = 'invisible = 0';
		if(!empty($paramarr['fid'])) {
			$paramarr['fid'] = getdotstring($paramarr['fid'], 'int');
			if($paramarr['fid']) $wherearr[] = 'fid IN ('.$paramarr['fid'].')';
		}
		if(!empty($paramarr['tid'])) {
			$paramarr['tid'] = getdotstring($paramarr['tid'], 'int');
			if($paramarr['tid']) $wherearr[] = 'tid IN ('.$paramarr['tid'].')';
		}
		if(!empty($paramarr['first'])) {
			$wherearr[] = 'first = 1';
		}
		if(!empty($paramarr['attachment'])) {
			$wherearr[] = 'attachment = 1';
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
	$listcount = $_SGLOBAL['db_bbs']->result($_SGLOBAL['db_bbs']->query('SELECT COUNT(*) FROM '.tname('posts', 1).' '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//query
if($listcount) {
	
	if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
	if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
	
	$query = $_SGLOBAL['db_bbs']->query($sqlstring);
	$aids = $dot = '';
	$theblockarr['text'] = array();
	while($post = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		//附件
		if($post['attachment']) {
			$aids .= $dot.$post['pid'];
			$dot = ', ';
		}
		
		//链接
		$post['url'] = getbbsurl('viewthread.php', array('tid'=>$post['tid']));
		
		//标题处理
		if(!empty($post['subject'])) $post['subjectall'] = $post['subject'];
		if(!empty($post['subject']) && !empty($paramarr['subjectlen'])) {
			$post['subject'] = cutstr($post['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}
		
		//内容
		if(!empty($post['message']) && !empty($paramarr['messagelen'])) {
			$post['message'] = trim(stripbbcode(strip_tags($post['message'])));
			$post['message'] = trim(cutstr($post['message'], $paramarr['messagelen'], $paramarr['messagedot']));
		}
		if(empty($post['subject']) && !empty($post['message'])) {
			$post['subject'] = $post['subjectall'] = cutstr(trim(stripbbcode(strip_tags($post['message']))), 40, 1);
		}

		$theblockarr['text'][] = $post;
	}
	
	//附件处理
	if($aids) {
		$sqlstring = 'SELECT * FROM '.tname('attachments', 1).' WHERE pid IN ('.$aids.')';
		$query = $_SGLOBAL['db_bbs']->query($sqlstring);
		while($attach = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			$value['attachment'] = getbbsattachment($value);
			$theblockarr[$attach['pid']][] = $attach;
		}
	}
	unset($aids, $dot);
}

?>