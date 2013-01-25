<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：信息TAG

	$RCSfile: block_spacetag_query.inc.php,v $
	$Revision: 1.12 $
	$Date: 2007/04/27 19:26:08 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(empty($paramarr['sql'])) {
	
	$wherearr = array();
	if(!empty($paramarr['type'])) {
		$paramarr['type'] = getdotstring($paramarr['type'], 'char', false, $_SGLOBAL['type']);
		if($paramarr['type']) $wherearr[] = 'i.type IN ('.$paramarr['type'].')';
	}

	if(!empty($paramarr['subtype'])) {
		$paramarr['subtype'] = getdotstring($paramarr['subtype'], 'char', false, array('goods', 'auction', 'file', 'music', 'video', 'flash'));
		$wherearr[] = 'i.subtype IN ('.$paramarr['subtype'].')';
	}

	if(!empty($paramarr['haveattach'])) {
		$wherearr[] = 'i.haveattach = 1';
	}

	if(!empty($paramarr['uid'])) {
		$paramarr['uid'] = getdotstring($paramarr['uid'], 'int');
		if($paramarr['uid']) $wherearr[] = 'i.uid IN ('.$paramarr['uid'].')';
	}

	if(!empty($paramarr['digest'])) {
		$paramarr['digest'] = getdotstring($paramarr['digest'], 'int');
		if($paramarr['digest']) $wherearr[] = 'i.digest IN ('.$paramarr['digest'].')';
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
	
	$wherearr[] = 'i.folder=1';
	
	$innersql = implode(' AND ', $wherearr);
	
	$sql = array();
	$sql['select'] = 'SELECT st.tagid, i.*';
	$sql['from'] = 'FROM '.tname('spacetags').' st INNER JOIN '.tname('spaceitems').' i ON i.itemid=st.itemid AND '.$innersql;
	$sql['join'] = '';

	
	if(!empty($paramarr['showdetail'])) {
		if(str_replace("'", '', $paramarr['type'])) {
			$sql['select'] .= ', ii.*';
			$sql['join'] .= ' LEFT JOIN '.tname(gettypetablename(str_replace("'", '', $paramarr['type']))).' ii ON ii.itemid=st.itemid';
		}
	}
	
	//where
	$wherearr = array();
	$paramarr['tagid'] = getdotstring($paramarr['tagid'], 'int');
	if($paramarr['tagid']) {
		$wherearr[] = 'st.tagid IN ('.$paramarr['tagid'].')';
	}
	
	if(!empty($paramarr['dateline'])) {
		$paramarr['dateline'] = intval($paramarr['dateline']);
		if($paramarr['dateline']) $wherearr[] = 'st.dateline >= '.($_SGLOBAL['timestamp']-$paramarr['dateline']);
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
	$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) '.$sql['from'].' '.$sql['where']), 0);
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
		} elseif($value['type'] == 'link') {
			if(!empty($value['url'])) $value['linkurl'] = $value['url'];
			$value['url'] = geturl('uid/'.$value['uid'].'/action/viewspace/itemid/'.$value['itemid']);
		} else {
			$value['url'] = geturl('uid/'.$value['uid'].'/action/viewspace/itemid/'.$value['itemid']);
		}

		//相关tag
		if(!empty($value['relativetags'])) $value['relativetags'] = $value['tags'] = unserialize($value['relativetags']);
		
		//分类名
		if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
		
		//图片处理
		if($value['type'] == 'video' && empty($value['subtype'])) {
			//视频
			if(!empty($value['image'])) {
				$value['image'] = S_URL.'/'.$value['image'];
			} else {
				$value['image'] = S_URL.'/images/base/novideo.jpg';//正在转换
			}
			$value['thumb'] = $value['image'];
		}  else {
			//其他
			if(empty($value['thumb'])) {//商品
				$value['thumb'] = S_URL.'/images/base/nopic.gif';
			} else {
				$value['thumb'] = A_URL.'/'.$value['thumb'];
			}
			if(empty($value['image'])) {//图片
				$value['image'] = S_URL.'/images/base/nopic.gif';
			} else {
				$value['image'] = A_URL.'/'.$value['image'];
			}
		}
		
		$theblockarr[] = $value;
	}
}

?>