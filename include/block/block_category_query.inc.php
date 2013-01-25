<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：分类

	$RCSfile: block_category_query.inc.php,v $
	$Revision: 1.10 $
	$Date: 2007/04/12 20:40:21 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {

	$sql = array();
	
	$sql['select'] = 'SELECT c.*';
	$sql['from'] = 'FROM '.tname('categories').' c';

	$wherearr = array();
	//where
	if(!empty($paramarr['catid'])) {
		$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
		if($paramarr['catid']) $wherearr[] = 'c.catid IN ('.$paramarr['catid'].')';
	} else {
		if(!empty($paramarr['type'])) {
			$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, array_merge(array('space', 'group'), $_SGLOBAL['type']));
			if($paramarr['type']) $wherearr[] = 'c.type IN ('.$paramarr['type'].')';
		}
		
		if(!empty($paramarr['isroot'])) {
			$paramarr['isroot'] = intval($paramarr['isroot']);
			if($paramarr['isroot'] == 1) {
				$wherearr[] = 'c.upid < 1';
			} elseif($paramarr['isroot'] == 2) {
				if(!empty($paramarr['upid'])) {
					$paramarr['upid'] = getdotstring($paramarr['upid'], 'int');
					if($paramarr['upid']) $wherearr[] = 'c.upid IN ('.$paramarr['upid'].')';					
				} else {
					$wherearr[] = 'c.upid > 0';
				}
			}
		} else {
			if(!empty($paramarr['upid'])) {
				$paramarr['upid'] = getdotstring($paramarr['upid'], 'int');
				if($paramarr['upid']) $wherearr[] = 'c.upid IN ('.$paramarr['upid'].')';
			}
		}
		
		if(!empty($paramarr['ischannel'])) {
			$paramarr['ischannel'] = intval($paramarr['ischannel']);
			if($paramarr['ischannel'] == 1) {
				$wherearr[] = 'c.ischannel = 1';
			} elseif($paramarr['ischannel'] == 2) {
				$wherearr[] = 'c.ischannel = 0';
			}
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
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('categories').' c '.$sql['where']), 0);
	if($listcount) {
		$urlarr = $_SGET;
		unset($urlarr['page']);
		$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_SGET['page'], $urlarr, 0);
	}
}

//查询数据
if($listcount) {
	//预处理
	//查询
	$query = $_SGLOBAL['db']->query($sqlstring);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {

		//链接
		$value['url'] = geturl('action/category/catid/'.$value['catid']);
		
		//图片封面
		if(!empty($value['image'])) {
			$value['image'] = A_URL.'/'.$value['image'];
		} else {
			$value['image'] = S_URL.'/images/base/nopic.gif';
		}
		
		//图片封面
		if(!empty($value['thumb'])) {
			$value['thumb'] = A_URL.'/'.$value['thumb'];
		} else {
			$value['thumb'] = S_URL.'/images/base/nopic.gif';
		}

		$theblockarr[] = $value;
	}
}

?>