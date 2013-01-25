<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：文件

	$RCSfile: block_spacefile_query.inc.php,v $
	$Revision: 1.20 $
	$Date: 2007/04/06 19:12:53 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	$sql = array();
	$sql['select'] = 'SELECT i.*';
	$sql['from'] = 'FROM '.tname('spaceitems').' i';
	$sql['join'] = '';

	if(!empty($paramarr['showdetail']) && empty($paramarr['notype'])) {
		$sql['select'] = 'SELECT ii.*, i.*';
		$sql['join'] .= ' LEFT JOIN '.tname('spacefiles').' ii ON ii.itemid=i.itemid ';
	}
	if(!empty($paramarr['showspacename'])) {
		$sql['select'] .= ', u.username, u.spacename, u.spaceallnum, u.spaceblognum, u.spaceimagenum, u.spacefilenum, u.spacegoodsnum, u.spacelinknum, u.province, u.city, u.domain, u.islock, u.isstar, u.photo';
		$sql['join'] .= ' LEFT JOIN '.tname('userspaces').' u ON u.uid=i.uid';
	}
	if(!empty($paramarr['showgroupname'])) {
		$sql['select'] .= ', g.groupname AS g_groupname, g.catid AS g_catid, g.uid AS g_uid, g.username AS g_username, g.usernum AS g_usernum, g.lastpost AS g_lastpost, g.dateline AS g_dateline, g.logo AS g_logo';
		$sql['join'] .= ' LEFT JOIN '.tname('groups').' g ON g.gid=i.gid';
	}
	$wherearr = array();

	//where
	if(!empty($paramarr['itemid'])) {
		$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
		if($paramarr['itemid']) $wherearr[] = 'i.itemid IN ('.$paramarr['itemid'].')';
	} else {
		//圈子
		if(!empty($paramarr['gid'])) {
			$paramarr['gid'] = getdotstring($paramarr['gid'], 'int');
			if($paramarr['gid']) $wherearr[] = 'i.gid IN ('.$paramarr['gid'].')';
		}
		if(empty($paramarr['gid'])) {
			if(!empty($paramarr['allgroup'])) $wherearr[] = 'i.gid != 0';
		}

		//作者
		if(!empty($paramarr['uid'])) {
			$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
			if($paramarr['uid']) $wherearr[] = 'i.uid IN ('.$paramarr['uid'].')';
		}

		//分类
		if(!empty($paramarr['catid'])) {
			$paramarr['catid'] = getdotstring($paramarr['catid'], 'int');
			if($paramarr['catid']) $wherearr[] = 'i.catid IN ('.$paramarr['catid'].')';
		}

		//限制
		if(empty($paramarr['catid']) && empty($paramarr['notype'])) {
			$wherearr[] = 'i.type=\'file\'';
		}
		$wherearr[] = 'i.folder=1';

		//站点审核
		if(!empty($paramarr['grade'])) {
			$paramarr['grade'] = getdotstring($paramarr['grade'], 'int');
			if(!empty($paramarr['grade'])) $wherearr[] = 'i.grade IN ('.$paramarr['grade'].')';
		} else {
			if(empty($paramarr['uid'])) {
				if(!empty($_SCONFIG['needcheck'])) {
					$wherearr[] = 'i.grade>0';
				}
			}
		}

		if(!empty($paramarr['digest'])) {
			$paramarr['digest'] = getdotstring($paramarr['digest'], 'int');
			if($paramarr['digest']) $wherearr[] = 'i.digest IN ('.$paramarr['digest'].')';
		}

		if(!empty($paramarr['top'])) {
			$paramarr['top'] = getdotstring($paramarr['top'], 'int');
			if($paramarr['top']) $wherearr[] = 'i.top IN ('.$paramarr['top'].')';
		}
		
		if(!empty($paramarr['dateline'])) {
			$paramarr['dateline'] = intval($paramarr['dateline']);
			if($paramarr['dateline']) $wherearr[] = 'i.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
		}

		if(!empty($paramarr['lastpost'])) {
			$paramarr['lastpost'] = intval($paramarr['lastpost']);
			if($paramarr['lastpost']) $wherearr[] = 'i.lastpost >= '.($_SGLOBAL['timestamp']-$paramarr['lastpost']);
		}

		$scopequery = getscopequery('i', 'viewnum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('i', 'replynum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('i', 'trackbacknum', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('i', 'goodrate', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;

		$scopequery = getscopequery('i', 'badrate', $paramarr);
		if(!empty($scopequery)) $wherearr[] = $scopequery;
		
		//圈子精华
		if(isset($paramarr['gdigest'])) {
			$paramarr['gdigest'] = intval($paramarr['gdigest']);
			if($paramarr['gdigest'] >= 0) {
				$wherearr[] = 'i.gdigest=\''.$paramarr['gdigest'].'\'';
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

//multi
$listcount = 1;
if(!empty($paramarr['perpage'])) {
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' i '.$sql['where']), 0);
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
	
	if(!empty($paramarr['showcategory'])) {
		include_once(S_ROOT.'./data/system/category.cache.php');
	}
	
	//查询
	$query = $_SGLOBAL['db']->query($sqlstring);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		
		//处理
		$value['subjectall'] = $value['subject'];
		if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
			$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}
	
		if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
			$value['message'] = trim(strip_tags($value['message']));
			$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
		}
	
		//类型
		if(!empty($value['type'])) $value['typename'] = $lang[$value['type']];
		
		//链接
		if($value['type'] == 'news') {
			$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
		} else {
			$value['url'] = geturl('uid/'.$value['uid'].'/action/viewspace/itemid/'.$value['itemid']);
		}
		
		//用户头像
		if(!empty($paramarr['showspacename'])) {
			$value['photo'] = getphoto($value['uid'], $value['photo']);
		}

		//相关tag
		if(!empty($value['relativetags'])) $value['relativetags'] = $value['tags'] = unserialize($value['relativetags']);
		
		//分类名
		if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
		
		$theblockarr[] = $value;
	}
}

?>