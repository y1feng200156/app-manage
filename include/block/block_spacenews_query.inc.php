<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	模块查询处理：资讯

	$RCSfile: block_spacenews_query.inc.php,v $
	$Revision: 1.21 $
	$Date: 2007/04/10 17:13:58 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$_SGLOBAL['attachsql'] = 'a.aid AS a_aid, a.type AS a_type, a.itemid AS a_itemid, a.uid AS a_uid, a.dateline AS a_dateline, a.filename AS a_filename, a.subject AS a_subject, a.attachtype AS a_attachtype, a.isimage AS a_isimage, a.size AS a_size, a.filepath AS a_filepath, a.thumbpath AS a_thumbpath, a.downloads AS a_downloads';

if(empty($paramarr['sql'])) {
	$sql = array();
	$sql['select'] = 'SELECT i.*';
	$sql['from'] = 'FROM '.tname('spaceitems').' i';
	$sql['join'] = '';

	$wherearr = array();
	
	//where
	if(!empty($paramarr['itemid'])) {
		$paramarr['itemid'] = getdotstring($paramarr['itemid'], 'int');
		if($paramarr['itemid']) $wherearr[] = 'i.itemid IN ('.$paramarr['itemid'].')';
	} else {
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
			$wherearr[] = 'i.type=\'news\'';
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

		if(!empty($paramarr['haveattach'])) {
			$wherearr[] = 'i.haveattach = 1';
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

if($listcount) {
	//预处理
	if(empty($paramarr['subjectdot'])) $paramarr['subjectdot'] = 0;
	if(empty($paramarr['messagedot'])) $paramarr['messagedot'] = 0;
	
	if(!empty($paramarr['showcategory'])) {
		include_once(S_ROOT.'./data/system/category.cache.php');
	}

	$query = $_SGLOBAL['db']->query($sqlstring);
	$allitemids = $itemids = array();
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		//处理
		$value['subjectall'] = $value['subject'];
		if(!empty($value['subject']) && !empty($paramarr['subjectlen'])) {
			$value['subject'] = cutstr($value['subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
		}

		//链接
		$value['url'] = geturl('action/viewnews/itemid/'.$value['itemid']);
		
		//附件
		if($value['haveattach'] && $value['hash']) {
			$itemids[] = $value['itemid'];
		}
		$allitemids[] = $value['itemid'];

		//相关tag
		if(!empty($value['relativetags'])) $value['relativetags'] = $value['tags'] = unserialize($value['relativetags']);
		
		//分类名
		if(!empty($_SGLOBAL['category'][$value['catid']])) $value['catname'] = $_SGLOBAL['category'][$value['catid']];
		
		//附件
		if(!empty($value['haveattach']) && ($value['type'] == 'blog' || $value['type'] == 'news')) $value['subject'] = $value['subject'].$lang['block_image'];
		
		$theblockarr[$value['itemid']] = $value;
	}
	
	//分页内容处理/取第一页
	if(!empty($paramarr['showdetail'])) {
		if(!empty($allitemids)) {
			$theitemarr = array();
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacenews').' WHERE itemid IN (\''.implode('\',\'', $allitemids).'\') ORDER BY nid');
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				if(empty($theitemarr[$value['itemid']])) {
					if(!empty($value['message']) && !empty($paramarr['messagelen'])) {
						$value['message'] = strip_tags(trim($value['message']));
						$value['message'] = trim(cutstr($value['message'], $paramarr['messagelen'], $paramarr['messagedot']));
					}
					$theitemarr[$value['itemid']] = 1;
					$theblockarr[$value['itemid']] = array_merge($theblockarr[$value['itemid']], $value);
				}
			}
		}
	}

	if(!empty($paramarr['showattach'])) {
		$attacharr = array();
		if(!empty($itemids)) {
			$query = $_SGLOBAL['db']->query('SELECT '.$_SGLOBAL['attachsql'].' FROM '.tname('attachments').' a WHERE a.itemid IN (\''.implode('\',\'', $itemids).'\') ORDER BY a.dateline');
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				//处理
				if(!empty($attacharr[$value['a_itemid']])) continue;
				
				$value['a_subjectall'] = $value['a_subject'];
				if(!empty($value['a_subject']) && !empty($paramarr['subjectlen'])) {
					$value['a_subject'] = cutstr($value['a_subject'], $paramarr['subjectlen'], $paramarr['subjectdot']);
				}
				//附件处理
				if(!empty($value['a_thumbpath'])) $value['a_thumbpath'] = A_URL.'/'.$value['a_thumbpath'];
				if(!empty($value['a_filepath'])) $value['a_filepath'] = A_URL.'/'.$value['a_filepath'];
				if(empty($value['a_thumbpath'])) {
					if(empty($value['a_filepath'])) {
						$value['a_thumbpath'] = S_URL.'/images/base/nopic.gif';
					} else {
						$value['a_thumbpath'] = $value['a_filepath'];
					}
				}
				if(empty($value['a_filepath'])) $value['a_filepath'] = $value['a_thumbpath'];
	
				$attacharr[$value['a_itemid']] = $value;
				$theblockarr[$value['a_itemid']] = array_merge($theblockarr[$value['a_itemid']], $value);
			}
		}
	}
}

?>