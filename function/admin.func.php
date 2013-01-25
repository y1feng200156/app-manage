<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	管理员后台函数集合

	$RCSfile: admin.func.php,v $
	$Revision: 1.115.8.1 $
	$Date: 2007/05/30 21:43:04 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//显示扩充信息选择列表
function prefieldhtml($thevalue, $prefieldarr, $var, $input=1, $size='20', $isarray=0) {
	global $alang;
	
	if($isarray) {
		$optionstr = '';
		foreach ($prefieldarr as $nakey => $navalue) {
			$optionstr .= '<option value="'.$nakey.'">'.$navalue.'</option>';
		}
	} else {
		if(empty($prefieldarr[$var])) {
			$vararr = array();
		} else {
			$vararr = $prefieldarr[$var];
		}
		$optionstr = '';
		foreach ($vararr as $navalue) {
			$optionstr .= '<option value="'.$navalue['value'].'">'.$navalue['value'].'</option>';
			if(empty($thevalue[$var]) && !empty($navalue['isdefault'])) {
				$thevalue[$var] = $navalue['value'];
			}
		}
	}
	$varstr = '';
	if($input) {
		if(empty($thevalue[$var])) $thevalue[$var] = '';
		$varstr .= '<input name="'.$var.'" type="text" id="'.$var.'" size="'.$size.'" value="'.$thevalue[$var].'" />';
		$varstr .= ' <select name="varop" onchange="changevalue(\''.$var.'\', this.value)">';
		$varstr .= '<option value="">'.$alang['prefield_option_'.$var].'</option>';
	} else {
		$varstr .= '<select name="'.$var.'">';
		if(!empty($optionstr)) {
			$optionstr = str_replace('value="'.$thevalue[$var].'"', 'value="'.$thevalue[$var].'" selected', $optionstr);
		}
	}
	
	$varstr .= $optionstr;
	$varstr .= '</select>';
	return $varstr;
}

//显示消息的函数
function showmessage($message, $gotourl='') {
	global $_SCONFIG, $alang;
	
	include_once(S_ROOT.'./language/admincp_message.lang.php');

	obclean();
	if(!empty($amlang[$message])) $message = $amlang[$message];
	if($gotourl) {
		$btn = '<a href="'.$gotourl.'">'.$alang['button_ok'].'</a>';
		$gotourl = '<meta http-equiv="refresh" content="2;url='.$gotourl.'">';
	} else {
		$btn = '<a href="javascript:history.back();" class="back">'.$alang['button_back'].'</a>';
	}
	
	print <<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$_SCONFIG[charset]" />
	$gotourl
	<title>Admin CP Message - Powered By SupeSite</title>
	<link rel="stylesheet" type="text/css" href="templates/$_SCONFIG[template]/css/style.css" />
	</head>
	<body>
	<table summary="message" id="notice" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td id="notice_message">$message</td>
		</tr>
		<tr>
			<td id="notice_links">$btn</td>
		</tr>
	</table>
	</body>
	</html>
END;
	exit;
}

//删除生成的html
function deleteitemhtml($itemidarr) {
	global $_SGLOBAL, $type;

	$id = $_SGLOBAL['supe_uid'];
	foreach ($itemidarr as $itemid) {
		if($type == 'news') {
			$id = $itemid;
		}
		$idvalue = ($id>9)?substr($id, -2, 2):$id;
		$filedir = H_DIR.'/'.$idvalue;
		if(is_dir($filedir)) {
			$filearr = sreaddir($filedir);
			foreach ($filearr as $file) {
				if(preg_match("/view(space|news)(.*)\_$itemid(\.|\_)/i", $file)) {
					@unlink($filedir.'/'.$file);
				}
			}
		}
	}
}

//显示模块的页面代码
function echolabel($blockarr, $theblcokvalue) {
	if(!empty($blockarr) && is_array($blockarr)) {
		foreach ($blockarr as $bkey => $bvalue) {
			if(!isset($bvalue['alang'])) $bvalue['alang'] = '';
			if(!isset($bvalue['options'])) $bvalue['options'] = array();
			if(!isset($bvalue['other'])) $bvalue['other'] = '';
			if(!isset($bvalue['text'])) $bvalue['text'] = '';
			if(!isset($bvalue['check'])) $bvalue['check'] = '';
			if(!isset($bvalue['radio'])) $bvalue['radio'] = '';
			if(!isset($bvalue['size'])) $bvalue['size'] = '';
			if(!isset($theblcokvalue[$bkey])) $theblcokvalue[$bkey] = '';
			if(!isset($bvalue['width'])) $bvalue['width'] = '';
			$labelarr = array('type'=>$bvalue['type'], 'alang'=>$bvalue['alang'], 'name'=>$bkey, 'size'=>$bvalue['size'], 'text'=>$bvalue['text'], 'check'=>$bvalue['check'], 'radio'=>$bvalue['radio'], 'options'=>$bvalue['options'], 'other'=>$bvalue['other'], 'width'=>$bvalue['width'], 'value'=>$theblcokvalue[$bkey]);
			if($bkey == 'order') {
				if(!isset($theblcokvalue['order'])) $theblcokvalue['order'] = '';
				if(!isset($theblcokvalue['sc'])) $theblcokvalue['sc'] = '';
				$labelarr['order'] = $theblcokvalue['order'];
				$labelarr['sc'] = $theblcokvalue['sc'];
			}
			echo label($labelarr);
		}
	}
}

//将两个数字构成一个范围串
function getscopestring($var, $array) {
	$result = '';
	$array[0] = intval($array[0]);
	$array[1] = intval($array[1]);
	if($array[1] > $array[0]) {
		$result = $var.'/'.$array[0].','.$array[1];
	}
	return $result;
}

//删除采集到本地的文件
function deletelocalfile($robotid, $isitemarr=0) {
	global $_SGLOBAL;
	
	$filearr = array();
	if($isitemarr) {
		$wheresql = 'i.itemid IN ('.simplode($_POST['item']).')';
	} else {
		$wheresql = 'i.robotid=\''.$robotid.'\'';
	}
	$query = $_SGLOBAL['db']->query('SELECT i.isimport, ii.* FROM '.tname('robotitems').' i LEFT JOIN '.tname('robotmessages').' ii ON ii.itemid=i.itemid WHERE '.$wheresql);
	
	while ($message = $_SGLOBAL['db']->fetch_array($query)) {
		if(!$message['isimport']) {
			if(!empty($message['picurls'])) {
				$thefilearr = unserialize($message['picurls']);
				$filearr = array_merge($filearr, $thefilearr);
			}
			if (!empty($message['flashurls'])) {
				$thefilearr = unserialize($message['flashurls']);
				$filearr = array_merge($filearr, $thefilearr);
			}
		}
	}
	if(!empty($filearr)) {
		foreach ($filearr as $filepath) {
			@unlink($filepath);
		}
	}	
}

//获取模块的风格列表
function getstyle($tpltype) {
	global $_SGLOBAL, $alang;
	$stylearr = array();
	//默认模板
	$stylearr[] = array(
		'tplid' => 0,
		'tplname' => $alang['style_default_tplname'],
		'tplnote' => $alang['style_default_tplnote'],
		'tpltype' => $tpltype,
		'tplfilepath' => 'data'
	);
	$query = $_SGLOBAL['db']->query('SELECT style.* FROM '.tname('styles').' style WHERE style.tpltype=\''.$tpltype.'\'');
	while ($style = $_SGLOBAL['db']->fetch_array($query)) {
		$stylearr[] = $style;
	}
	return $stylearr;
}

//获取论坛版块列表
function getbbsforum($isblog=0, $space='|----') {
	global $_SGLOBAL;
	
	dbconnect(1);
	
	$forumarr = array();
	if($isblog) {
		if(B_VER == '4') {
			$wheresql = ' AND (type=\'group\' || allowblog=\'1\')';
		} else {
			$wheresql = ' AND (type=\'group\' || allowshare=\'1\')';
		}
	} else {
		$wheresql = '';
	}
	
	include_once(S_ROOT.'./class/tree.class.php');
	$tree = new Tree('blog');
	$minfup = '';
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('forums', 1).' WHERE status>0'.$wheresql.' ORDER BY fup, displayorder');
	while ($forum = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		if($minfup == '') $minfup = $forum['fup'];
		$tree->setNode($forum['fid'], $forum['fup'], $forum);
	}
	//根目录
	$listarr = array();
	$categoryarr = $tree->getChilds($minfup);
	foreach ($categoryarr as $key => $catid) {
		$cat = $tree->getValue($catid);
		$cat['pre'] = $tree->getLayer($catid, $space);
		$listarr[$cat['fid']] = $cat;
	}
	return $listarr;
}

//获取论坛主题分类
function getbbstype() {
	global $_SGLOBAL;
	
	dbconnect(1);
	$typearr = array();
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('threadtypes', 1).' ORDER BY displayorder');
	while ($type = $_SGLOBAL['db_bbs']->fetch_array($query)) {	
		$typearr[$type['typeid']] = $type['name'];
	}
	return $typearr;
}

//标题前缀属性图标
function getsubjectpre($listvalue) {
	global $alang;
	$subjectpre = '';
	
	if($listvalue['digest'] >0) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/icon_digest.gif" align="absmiddle" alt="'.$alang['admin_func_digest'].numtoI($listvalue['digest']).'"> ';
	}
	if($listvalue['top'] > 0) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/top.gif" align="absmiddle" alt="'.$alang['admin_func_top'].numtoI($listvalue['top']).'"> ';
	}
	if(empty($listvalue['allowreply'])) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/noreply.gif" align="absmiddle" alt="'.$alang['admin_func_noreply'].'"> ';
	}
	if(!empty($listvalue['haveattach'])) {
		$subjectpre .= '<img src="'.S_URL.'/images/base/haveattach.gif" align="absmiddle" alt="'.$alang['admin_func_attachment'].'"> ';
	}
	return $subjectpre;
}

//删除空间
function deleteuserspaces($uid) {
	global $_SGLOBAL;

	$_SGLOBAL['db']->query("DELETE FROM ".tname('corpus')." WHERE authorid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('customfields')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('friends')." WHERE (uid='$uid') OR (frienduid='$uid')");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('guestbooks')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('itemtypes')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('members')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagcache')." WHERE uid='$uid'");
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('usercss')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('userfields')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('userlinks')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('visitors')." WHERE (uid='$uid') OR (visitoruid='$uid')");
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spacecomments')." WHERE (uid='$uid') OR (authorid='$uid')");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tracks')." WHERE uid='$uid'");
	
	clearspacecache($uid);

	$_SGLOBAL['db']->query("DELETE FROM ".tname('favorites')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('goodsprice')." WHERE uid='$uid'");
	
	deleteitems('uid', $uid);
	
	if(!@unlink($path=getuserfile($uid, 'css'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, 'jpg'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, 'gif'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, 'html'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, 'diy'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, 'pdf'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, 'xml'))) errorlog('File', 'Unlink '.$path.' Error.');
	if(!@unlink($path=getuserfile($uid, ''))) errorlog('File', 'Unlink '.$path.' Error.');

	$_SGLOBAL['db']->query("DELETE FROM ".tname('userspacefields')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('userspaces')." WHERE uid='$uid'");


	dbconnect(1);
	$_SGLOBAL['db_bbs']->query('UPDATE '.tname('members', 1)." SET xspacestatus='0' WHERE uid='$uid'");
}

/**
 * 采集器方法
 */
function messageaddtodb($msgarr, $robotid, $itemid=0) {
	global $_SGLOBAL;
	
	if(!$itemid) {
		$insertsqlarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'robotid' => $robotid,
			'robottime' => $_SGLOBAL['timestamp'],
			'subject' => saddslashes($msgarr['subject'])
		);
		if(!empty($msgarr['itemfrom'])) $insertsqlarr['itemfrom'] = saddslashes($msgarr['itemfrom']);
		if(!empty($msgarr['author'])) $insertsqlarr['author'] = saddslashes($msgarr['author']);
		if(!empty($msgarr['dateline'])) $insertsqlarr['dateline'] = $msgarr['dateline'];
		$itemid = inserttable('robotitems', $insertsqlarr, 1, 1);
	}
	
	//INSERT MESSAGE
	$insertsqlarr = array(
		'itemid' => $itemid,
		'robotid' => $robotid
	);
	if(!empty($msgarr['message'])) $insertsqlarr['message'] = saddslashes($msgarr['message']);
	if(!empty($msgarr['picarr'])) $insertsqlarr['picurls'] = saddslashes(serialize($msgarr['picarr']));
	if(!empty($msgarr['flasharr'])) $insertsqlarr['flashurls'] = saddslashes(serialize($msgarr['flasharr']));
	inserttable('robotmessages', $insertsqlarr, 0, 1);
	return $itemid;
}


?>
