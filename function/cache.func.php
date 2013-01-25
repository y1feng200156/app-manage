<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	更新CACHE的一些函数

	$RCSfile: cache.func.php,v $
	$Revision: 1.30 $
	$Date: 2007/04/12 20:40:21 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//更新用户组CACHE
function updategroupcache() {
	global $_SGLOBAL;
	
	dbconnect(1);

	$grouptitles = array();
	$query = $_SGLOBAL['db_bbs']->query("SELECT groupid, grouptitle FROM ".tname('usergroups', 1));
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$grouptitles[$value['groupid']] = $value['grouptitle'];
	}

	$_SGLOBAL['grouparr'] = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usergroups'));
	while ($group = $_SGLOBAL['db']->fetch_array($query)) {
		$group['grouptitle'] = $grouptitles[$group['groupid']];
		$_SGLOBAL['grouparr'][$group['groupid']] = $group;
	}

	$cachefile = S_ROOT.'./data/system/group.cache.php';
	$cachetext = '$_SGLOBAL[\'grouparr\']='.arrayeval($_SGLOBAL['grouparr']);
	writefile($cachefile, $cachetext, 'php');
}

//更新基本配置CACHE
function updatesettingcache() {

	global $_SGLOBAL, $_SSCONFIG, $lang;
	
	$_SSCONFIG = array();
	
	dbconnect();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('settings'));
	while ($set = $_SGLOBAL['db']->fetch_array($query)) {
		$_SSCONFIG[$set['variable']] = $set['value'];
	}

	//缩略图设置
	if(empty($_SSCONFIG['thumbarray'])) {
		$_SSCONFIG['thumbarray'] = array(
			'news' => array('400','300'),
			'blog' => array('400','300'),
			'image' => array('100','75'),
			'goods' => array('100','75'),
			'video' => array('320','240'),
			'space' => array('130','100')
		);
	} else {
		$_SSCONFIG['thumbarray'] = unserialize($_SSCONFIG['thumbarray']);
	}
	
	//播放器设置
	if(empty($_SSCONFIG['playerarr'])) {
		$_SSCONFIG['playerarr'] = array(
			'media' => 'wmv,wm,asf,asx,wma,mp3,mov,avi',
			'real' => 'rm,ra,rmvb,ram',
			'flash' => ''
		);
	} else {
		$_SSCONFIG['playerarr'] = unserialize($_SSCONFIG['playerarr']);
	}
	
	//播放器广告
	if(empty($_SSCONFIG['videoadarr'])) {
		$_SSCONFIG['videoadarr'] = array(
			'logo' => array('3', 'images/base/videologo.gif'),
			'ad' => array()
		);
	} else {
		$_SSCONFIG['videoadarr'] = unserialize($_SSCONFIG['videoadarr']);
	}

	//channel
	$_SSCONFIG['defaultchannel'] = '';
	$_SSCONFIG['channel'] = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('channels').' WHERE status>0 ORDER BY displayorder');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SSCONFIG['channel'][$value['nameid']] = $value;
		if($value['status'] == 2) {
			$_SSCONFIG['defaultchannel'] = $value['nameid'];
		}
	}

	//论坛配置
	dbconnect(1);
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('settings', 1));
	while ($value = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		if($value['variable'] == 'authkey') {
			$_SSCONFIG['sitekey'] = $value['value'];
		} elseif($value['variable'] == 'ftp') {
			$_SSCONFIG['bbs_ftp'] = unserialize($value['value']);
		}
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/config.cache.php';
	$cachetext = '$_SSCONFIG='.arrayeval($_SSCONFIG);
	writefile($cachefile, $cachetext, 'php');
}

//更新广告CACHE
function updateadcache() {
	global $_SGLOBAL;
	
	dbconnect();
	$adarr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('ads'));
	while ($ad = $_SGLOBAL['db']->fetch_array($query)) {
		$adarr[$ad['adid']] = $ad;
	}
	
	$cachefile = S_ROOT.'./data/system/ad.cache.php';
	$cachetext = '$_SGLOBAL[\'ad\']='.arrayeval($adarr);
	writefile($cachefile, $cachetext, 'php');
}

//更新页面特效
function updateeffectcache() {
	global $_SGLOBAL;
	
	dbconnect();
	$earr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('effects'));
	while ($e = $_SGLOBAL['db']->fetch_array($query)) {
		$earr[$e['eid']] = $e;
	}
	
	$cachefile = S_ROOT.'./data/system/effect.cache.php';
	$cachetext = '$_SGLOBAL[\'effect\']='.arrayeval($earr);
	writefile($cachefile, $cachetext, 'php');
}

//更新cron列表
function updatecronscache() {
	global $_SGLOBAL;
	
	dbconnect();
	$carr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('crons').' WHERE available>0');
	while ($cron = $_SGLOBAL['db']->fetch_array($query)) {
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cron['minute'] = explode("\t", $cron['minute']);
		$carr[$cron['cronid']] = $cron;
	}
	
	$cachefile = S_ROOT.'./data/system/crons.cache.php';
	$cachetext = '$_SGLOBAL[\'crons\']='.arrayeval($carr);
	writefile($cachefile, $cachetext, 'php');
}


//更新计划任务的CACHE
function updatecroncache($cronnextrun=0) {
	global $_SGLOBAL;

	if(empty($cronnextrun)) {
		dbconnect();
		$query = $_SGLOBAL['db']->query('SELECT nextrun FROM '.tname('crons').' WHERE available>0 AND nextrun>\''.$_SGLOBAL['timestamp'].'\' ORDER BY nextrun LIMIT 1');
		$cronnextrun = $_SGLOBAL['db']->result($query, 0);
	}
	if(empty($cronnextrun)) {
		$cronnextrun = $_SGLOBAL['timestamp'] + 2*3600;
	}

	$croncachefile = S_ROOT.'./data/system/cron.cache.php';
	$text = '$_SGLOBAL[\'cronnextrun\']='.$cronnextrun.';';
	writefile($croncachefile, $text, 'php');
}

//缓存论坛设置
function updatebbssetting() {
	global $_SGLOBAL, $_DCACHE;
	
	dbconnect(1);
	$_DCACHE['settings'] = array();
	
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('settings', 1));
	while ($set = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$_DCACHE['settings'][$set['variable']] = $set['value'];
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/bbs_settings.cache.php';
	$cachetext = '$_DCACHE[\'settings\']='.arrayeval($_DCACHE['settings']);
	writefile($cachefile, $cachetext, 'php');
}

//缓存论坛风格设置
function updatebbsstyle() {
	global $_SGLOBAL, $_DCACHE;
	
	dbconnect(1);
	$_DCACHE['style'] = array();
	
	$query = $_SGLOBAL['db_bbs']->query('SELECT value FROM '.tname('settings', 1).' WHERE variable=\'styleid\'');
	$styleid = $_SGLOBAL['db_bbs']->result($query, 0);
	if(empty($styleid)) $styleid = 1;
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('stylevars', 1).' WHERE styleid=\''.$styleid.'\'');
	while ($res = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$_DCACHE['style'][$res['variable']] = $res['substitute'];
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/bbs_style.cache.php';
	$cachetext = '$_DCACHE[\'style\']='.arrayeval($_DCACHE['style']);
	writefile($cachefile, $cachetext, 'php');
}

//缓存论坛语言屏蔽
function updatebbscensor() {
	global $_SGLOBAL, $_DCACHE;
	
	dbconnect(1);
	$_DCACHE['censor'] = array();
	$banned = $mod = array();
	$_DCACHE['censor'] = array('filter' => array(), 'banned' => '', 'mod' => '');
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('words', 1));
	while($censor = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$censor['find'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($censor['find'], '/'));
		switch($censor['replacement']) {
			case '{BANNED}':
				$banned[] = $censor['find'];
				break;
			case '{MOD}':
				$mod[] = $censor['find'];
				break;
			default:
				$_DCACHE['censor']['filter']['find'][] = '/'.$censor['find'].'/i';
				$_DCACHE['censor']['filter']['replace'][] = $censor['replacement'];
				break;
		}
	}
	if($banned) {
		$_DCACHE['censor']['banned'] = '/('.implode('|', $banned).')/i';
	}
	if($mod) {
		$_DCACHE['censor']['mod'] = '/('.implode('|', $mod).')/i';
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/bbs_censor.cache.php';
	$cachetext = '$_DCACHE[\'censor\']='.arrayeval($_DCACHE['censor']);
	writefile($cachefile, $cachetext, 'php');	
}


//缓存论坛bbcode设置
function updatebbsbbcode() {
	global $_SGLOBAL, $_DCACHE;
	
	dbconnect(1);
	$_DCACHE['bbcodes'] = $_DCACHE['smilies'] = array();
	
	$regexp = array	(1 => "/\[{bbtag}](.+?)\[\/{bbtag}\]/is",
		2 => "/\[{bbtag}=(['\"]?)(.+?)(['\"]?)\](.+?)\[\/{bbtag}\]/is",
		3 => "/\[{bbtag}=(['\"]?)(.+?)(['\"]?),(['\"]?)(.+?)(['\"]?)\](.+?)\[\/{bbtag}\]/is"
	);
	
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('bbcodes', 1).' WHERE available=\'1\'');
	while ($bbcode = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
		$bbcode['replacement'] = preg_replace("/([\r\n])/", '', $bbcode['replacement']);
		switch($bbcode['params']) {
			case 2:
				$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
				$bbcode['replacement'] = str_replace('{2}', '\\4', $bbcode['replacement']);
				break;
			case 3:
				$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
				$bbcode['replacement'] = str_replace('{2}', '\\5', $bbcode['replacement']);
				$bbcode['replacement'] = str_replace('{3}', '\\7', $bbcode['replacement']);
				break;
			default:
				$bbcode['replacement'] = str_replace('{1}', '\\1', $bbcode['replacement']);
				break;
		}
		$replace = $bbcode['replacement'];

		for($i = 0; $i < $bbcode['nest']; $i++) {
			$_DCACHE['bbcodes']['searcharray'][] = $search;
			$_DCACHE['bbcodes']['replacearray'][] = $replace;
		}
	}

	$_DCACHE['smilies'] = array('searcharray' => array(), 'replacearray' => array());
	$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('smilies', 1).' WHERE type=\'smiley\' ORDER BY LENGTH(code) DESC');
	while ($smiley = $_SGLOBAL['db_bbs']->fetch_array($query)) {
		$_DCACHE['smilies']['searcharray'][] = '/'.preg_quote(shtmlspecialchars($smiley['code']), '/').'/';
		$_DCACHE['smilies']['replacearray'][] = $smiley['url'];
		$_DCACHE['smilies']['display'][] = array('code'=>$smiley['code'], 'url'=>$smiley['url']);
	}

	//make cache
	$cachefile = S_ROOT.'./data/system/bbs_bbcodes.cache.php';
	$cachetext = '$_DCACHE[\'bbcodes\']='.arrayeval($_DCACHE['bbcodes']).";\r\n\r\n";
	$cachetext .= '$_DCACHE[\'smilies\']='.arrayeval($_DCACHE['smilies']).";\r\n\r\n";
	writefile($cachefile, $cachetext, 'php');
}

//更新站点公告
function updateannouncementcache() {
	global $_SGLOBAL;
	
	dbconnect();
	$earr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('announcements').' WHERE starttime < \''.$_SGLOBAL['timestamp'].'\' AND (endtime > \''.$_SGLOBAL['timestamp'].'\' OR endtime = 0) ORDER BY displayorder, starttime DESC, id DESC LIMIT 0,10');
	while ($e = $_SGLOBAL['db']->fetch_array($query)) {
		$earr[] = $e;
	}
	
	$cachefile = S_ROOT.'./data/system/announcement.cache.php';
	$cachetext = '$_SGLOBAL[\'announcement\']='.arrayeval($earr);
	writefile($cachefile, $cachetext, 'php');
}

//更新站点分类
function updatecategorycache() {
	global $_SGLOBAL;
	
	dbconnect();
	$carr = array();
	$query = $_SGLOBAL['db']->query('SELECT catid, upid, name, subcatid FROM '.tname('categories').' ORDER BY displayorder');
	while ($cat = $_SGLOBAL['db']->fetch_array($query)) {
		$carr[$cat['catid']] = $cat;
	}
	
	$cachefile = S_ROOT.'./data/system/category.cache.php';
	$cachetext = '$_SGLOBAL[\'category\']='.arrayeval($carr);
	writefile($cachefile, $cachetext, 'php');
}

?>