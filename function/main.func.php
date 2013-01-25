<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	主要函数

	$RCSfile: main.func.php,v $
	$Revision: 1.119.4.4 $
	$Date: 2007/05/30 21:43:04 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function parseparameter($param, $nofix=1) {
	global $_SCONFIG;

	$paramarr = array();

	if($nofix && !empty($_SCONFIG['pagepostfix'])) {
		if(strrpos($param, $_SCONFIG['pagepostfix'])) {
			$param = substr($param, 0, strrpos($param, $_SCONFIG['pagepostfix']));
		}
	}

	$sarr = explode('/', $param);
	if(empty($sarr)) return $paramarr;
	if(is_numeric($sarr[0])) $sarr = array_merge(array('uid'), $sarr);
	if(count($sarr)%2 != 0) $sarr = array_slice($sarr, 0, -1);
	for($i=0; $i<count($sarr); $i=$i+2) {
		if(!empty($sarr[$i+1])) $paramarr[$sarr[$i]] = addslashes(str_replace(array('/', '\\'), '', rawurldecode(stripslashes($sarr[$i+1]))));
	}
	return $paramarr;
}

function arraytostring($array, $dot='/') {
	$result = $comma = '';
	foreach ($array as $key => $value) {
		$value = trim($value);
		if($value != '') {
			$result .= $comma.$key.$dot.rawurlencode($value);
			$comma = $dot;
		}
	}
	return $result;
}

//将数组加上单引号,并整理成串
function simplode($sarr, $comma=',') {
	return '\''.implode('\''.$comma.'\'', $sarr).'\'';
}

function gethtmlfile($parray) {

	$htmlarr = array();
	$dirarr = array();
	$id = 0;

	if(empty($parray['page'])) {
		unset($parray['page']);
	} elseif($parray['page'] < 2) {
		unset($parray['page']);
	}
	if(!empty($parray['uid'])) {
		$id = $parray['uid'];
		if(!empty($parray['action'])) {
			if($parray['action'] == 'space' || $parray['action'] == 'spacelist') {
				unset($parray['action']);
			} elseif ($parray['action'] == 'viewspace') {
				unset($parray['action']);
			}
		}
	} elseif(!empty($parray['itemid'])) {
		$id = $parray['itemid'];
	} elseif(!empty($parray['tid'])) {
		$id = $parray['tid'];
	} elseif(!empty($parray['tagid'])) {
		$id = $parray['tagid'];
	} elseif(!empty($parray['catid'])) {
		$id = $parray['catid'];
	} elseif(!empty($parray['fid'])) {
		$id = $parray['fid'];
	}
	
	$htmlfilename = str_replace(array('action-', 'uid-', 'itemid-'), array('', '', ''), arraytostring($parray, '-'));
	if(!empty($id)) {
		$thedir = ($id>9)?substr($id, -2, 2):$id;
		if(!empty($parray['action'])) {
			if($parray['action'] == 'viewnews') {
				$htmlfilename = "n-{$id}";
				if(!empty($parray['page'])) $htmlfilename .= '-'.$parray['page'];
			} elseif($parray['action'] == 'viewthread') {
				$htmlfilename = "t-{$id}";
			}
		}
	}
	
	if(is_dir(H_DIR) || (!is_dir(H_DIR) && @mkdir(H_DIR))) {
		if(empty($id)) {
			$htmlarr['path'] = H_DIR.'/'.$htmlfilename.'.html';
			$htmlarr['url'] = H_URL.'/'.$htmlfilename.'.html';
		} else {
			$htmldir = H_DIR.'/'.$thedir;
			if(is_dir($htmldir) || (!is_dir($htmldir) && @mkdir($htmldir))) {
				$htmlarr['path'] = H_DIR.'/'.$thedir.'/'.$htmlfilename.'.html';
				$htmlarr['url'] = H_URL.'/'.$thedir.'/'.$htmlfilename.'.html';
			} else {
				$htmlarr['path'] = H_DIR.'/'.$htmlfilename.'.html';
				$htmlarr['url'] = H_URL.'/'.$htmlfilename.'.html';
			}
		}
	} else {
		$htmlarr['path'] = S_ROOT.'./'.$htmlfilename.'.html';
		$htmlarr['url'] = S_URL.'/'.$htmlfilename.'.html';
	}

	return $htmlarr;
}

function geturl($pstring, $urlmode=0) {
	
	global $_SGLOBAL, $_SCONFIG, $spaceself;

	//URL缓存
	$cachekey = $pstring.$urlmode;
	if(empty($_SGLOBAL['url_cache'])) $_SGLOBAL['url_cache'] = array();
	if(!empty($_SGLOBAL['url_cache'][$cachekey])) {
		return $_SGLOBAL['url_cache'][$cachekey];
	}
	
	//url结果
	$theurl = '';

	//强制php模式
	$isphp = !empty($spaceself)?1:strexists($pstring, 'php/1');
	
	//首页链接
	if($pstring == 'action/index') $pstring = '';
	
	//搜索友好模式
	if(!empty($_SCONFIG['htmlmode']) && $_SCONFIG['htmlmode'] == 2 && !$isphp && $urlmode != 1) {
		$htmlarr = array('uid'=>'', 'action'=>'', 'catid'=>'', 'fid'=>'', 'tagid'=>'', 'itemid'=>'', 'tid'=>'', 'type'=>'', 'view'=>'', 'mode'=>'', 'showpro'=>'', 'itemtypeid'=>'', 'page'=>'');
		$sarr = explode('/', $pstring);
		
		if(empty($sarr)) $sarr = array('action'=>'index');

		$htmlurlcheck = true;
		for($i=0; $i<count($sarr); $i=$i+2) {
			if(!empty($sarr[$i+1])) {
				if(key_exists($sarr[$i], $htmlarr)) {
					$htmlarr[$sarr[$i]] = addslashes(str_replace(array('/', '\\'), '', rawurldecode(stripslashes($sarr[$i+1]))));
				} else {
					$htmlurlcheck = false;
					break;
				}
			}
		}
		if($htmlurlcheck) {
			$htmls = gethtmlfile($htmlarr);
			if(file_exists($htmls['path'])) {
				$theurl = $htmls['url'];
			}
		}
	}

	//普通模式
	if(empty($theurl)) {
		if(empty($pstring)) {
			if($urlmode == 1) {
				$theurl = S_URL_ALL;
			} else {
				$theurl = S_URL;
			}
		} else {
			$pre = '';
			$para = str_replace('/', '-', $pstring);
			if($isphp) {
				$pre = '/index.php?';
			} else {
				if ($_SCONFIG['urltype'] == 5) {
					$pre = '/index.php/';
				} else {
					$pre = '/?';
				}
			}
			if(empty($para)) $pre = '/';
			
			if($urlmode == 1) {
				//全部路径
				$theurl = S_URL_ALL.$pre.$para;
			} elseif($urlmode == 2) {
				//处理
				$theurl = S_URL.$pre.$para;
				$theurl = url_remake($theurl);
			} else {
				//常规
				$theurl = S_URL.$pre.$para;
			}
		}
	}

	//url缓存
	$_SGLOBAL['url_cache'][$cachekey] = $theurl;
	
	return $theurl;
}

function ehtml($type, $updatetime=0) {
	global $_SGLOBAL, $_SGET, $_SHTML, $_SCONFIG, $lang;

	if($type == 'get') {
		$_SGLOBAL['htmlfile']['updatetime'] = $updatetime;
		if(empty($_SGET['php']) && !empty($_SGLOBAL['htmlfile']['path']) && file_exists($_SGLOBAL['htmlfile']['path'])) {
			sheader($_SGLOBAL['htmlfile']['url']);
		}
	} else {
		if(empty($_SHTML['maxpage']) && !empty($_SGLOBAL['htmlfile']['path'])) {
			$content = $_SGLOBAL['content'];
			$theurl = S_URL_ALL.'/index.php?'.arraytostring($_SHTML);
			$codearr = array(
				'url' => rawurlencode($theurl),
				'maketime' => $_SGLOBAL['timestamp'],
				'updatetime' => $_SGLOBAL['htmlfile']['updatetime'],
				'uid' => empty($_SHTML['uid'])?0:$_SHTML['uid'],
				'itemid' => empty($_SHTML['itemid'])?0:$_SHTML['itemid'],
				'action' => $_SHTML['action']
			);
	
			$code = rawurlencode(implode('/', $codearr));
			$content .= '
			<script language="javascript">
			<!--
			var Modified = new Date(document.lastModified);
			var copyright = document.getElementById("xspace-copyright");
			if(copyright) {
				copyright.innerHTML += "Last update: <a href=\"'.$theurl.'/php/1\" title=\"'.$lang['the_page_can_be_updated_immediately_hits'].'\">"+(Modified.getYear()<200?(Modified.getYear()+1900):Modified.getYear())+"-"+(Modified.getMonth()+1)+"-"+Modified.getDate()+" "+Modified.getHours()+":"+Modified.getMinutes()+":"+Modified.getSeconds() + "</a><br>";
			}
			document.write(\'<script src="'.S_URL.'/batch.html.php?code='.$code.'&amp;lastmodified=\' + Modified.getTime() + \'" type="text\/javascript" language="javascript"><\/script>\');
			//-->
			</script>';

			writefile($_SGLOBAL['htmlfile']['path'], $content);
		}
	}
}

function ob_out() {
	global $_SGLOBAL, $_SCONFIG;

	$_SGLOBAL['content'] = ob_get_contents();
	if($_SCONFIG['urltype'] != 4 && $_SCONFIG['urltype'] != 5) {
		$_SGLOBAL['content'] = preg_replace("/href\=\"(\S*?)\/(index\.php)?\?uid\-([0-9]+)\-?(\S*?)\"/i", 'href="\\1/?\\3/\\4"', $_SGLOBAL['content']);
		$_SGLOBAL['content'] = preg_replace("/href\=\"\S*?\/(index\.php)?\?(\S+?)\"/ie", "url_replace('\\2')", $_SGLOBAL['content']);
		obclean();
		echo $_SGLOBAL['content'];
	}

	if(D_BUG) include_once(S_ROOT.'./include/debug.inc.php');
}

function url_replace($para, $quote=1) {
	global $_SCONFIG;

	$para = str_replace(
		array(
			'action-viewnews-itemid',
			'action-viewspace-itemid',
			'action-viewthread-tid',
			'action-category-catid',
			'action-spacelist-type',
			'action-mygroup-gid',
			'action-index'
		),
		array(
			'viewnews',
			'viewspace',
			'viewthread',
			'category',
			'spacelist',
			'mygroup',
			''
		),
		$para
	);
	
	if($_SCONFIG['urltype'] == 3) {
		$pre = '/';
	} elseif($_SCONFIG['urltype'] == 2) {
		$pre = '/index.php/';
	} else {
		$pre = '/?';
	}
	
	if(empty($para)) {
		$para = '/';
	} elseif(substr($para, -1, 1) == '/') {
		$para = $pre.$para;
	} else {
		$para = $pre.$para.$_SCONFIG['pagepostfix'];
	}

	return empty($quote)?S_URL.$para:'href="'.S_URL.$para.'"';
}

function url_remake($url) {
	$url = preg_replace("/(\S*)\/(index\.php)?\?uid\-([0-9]+)\-?(\S*)/i", '\\1/?\\3/\\4', $url);
	$url = preg_replace("/\S*\/(index\.php)?\?(\S+)/ie", "url_replace('\\2', 0)", $url);
	return $url;
}

function sgmdate($timestamp, $dateformat='') {
	global $_SCONFIG;

	if(empty($dateformat)) {
		$dateformat = 'Y-m-d H:i:s';
	}
	if(!empty($timestamp)) {
		return gmdate($dateformat, $timestamp + $_SCONFIG['timeoffset'] * 3600);
	} else {
		return '';
	}
}

function tname($name, $isbbs=0) {
	global $tablepre, $tablepre_bbs, $dbname_bbs;

	if(empty($isbbs)) {
		//supesiete
		return $tablepre.$name;
	} else {
		//bbs
		return (empty($dbname_bbs)?'':'`'.$dbname_bbs.'`.').$tablepre_bbs.$name;
	}
}

function mydomain($domain) {
	global $_SGLOBAL;
	
	if($domain != 'www') {
		dbconnect();
		$query = $_SGLOBAL['db']->query('SELECT uid FROM '.tname('userspaces').' WHERE domain=\''.$domain.'\'');
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			sheader(geturl('uid/'.$value['uid']));
		}
		sheader(S_URL);
	}
}

function authcode($string, $operation, $key = '') {
	global $_SGLOBAL;
	
	$auth_key = !empty($key) ? $key : $_SGLOBAL['authkey'];

	$key = md5($auth_key);
	$key_length = strlen($key);

	$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);

	$rndkey = $box = array();
	$result = '';

	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}
}

function getuserspace($uid) {
	global $_SGLOBAL;

	$query = $_SGLOBAL['db']->query('SELECT uu.*, u.* FROM '.tname('userspaces').' u LEFT JOIN '.tname('userspacefields').' uu ON uu.uid=u.uid WHERE u.uid=\''.$uid.'\'');
	if($space = $_SGLOBAL['db']->fetch_array($query)) {
		return getspacedefault($space);
	} else {
		return array();
	}
}

function getClassAndMethod($name){
	$arr = split("::", $name);
	return $arr;
}

function getspacedefault($space) {
	global $lang;

	if(empty($space['photo'])) $space['photo'] = ''; //处理没有头像的问题
	$space['photo'] = getphoto($space['uid'], $space['photo']);
	if(empty($space['showblocks'])) $space['showblocks'] = 'blog,image,video,goods,file,link,group,bbs,friend,viewpro';
	if(empty($space['layout'])) $space['layout'] = 1;
	if(empty($space['choiceblockleft']) && empty($space['choiceblockright'])) $space['choiceblockleft'] = 'photo,action,calendar,archive,search,visitor,track,friend,guestbook,comment,favorite,userlink,music,information,rss';
	if(empty($space['choiceblockmain'])) $space['choiceblockmain'] = 'newblog,mythread,bbsthread,newimage,newgoods,newfile,newlink';
	if(empty($space['blognum'])) $space['blognum'] = 10;
	if(empty($space['imagenum'])) $space['imagenum'] = 8;
	if(empty($space['videonum'])) $space['videonum'] = 8;
	if(empty($space['goodsnum'])) $space['goodsnum'] = 10;
	if(empty($space['filenum'])) $space['filenum'] = 10;
	if(empty($space['linknum'])) $space['linknum'] = 10;
	if(empty($space['groupnum'])) $space['groupnum'] = 10;
	if(empty($space['threadnum'])) $space['threadnum'] = 10;
	if(empty($space['othernum'])) $space['othernum'] = 10;
	if(empty($space['spacemode'])) $space['spacemode'] = 'blog';//默认日志模式
	$space['gradenames'] = empty($space['gradenames'])?array('1'=>$lang['friend_grade1'],'2'=>$lang['friend_grade2'],'3'=>$lang['friend_grade3'],'4'=>$lang['friend_grade4'],'5'=>$lang['friend_grade5']):unserialize($space['gradenames']);
	$space['showcp'] = 0;
	return $space;
}

function getphoto($uid, $photo) {
	if(empty($photo)) {
		$photourl = S_URL.'/images/base/space_noface.gif';
	} else {
		$photourl = getuserfile($uid, $photo, 'url');
	}
	return $photourl;
}

function getcookie($db=0){
	global $_SGLOBAL, $cookiepre, $_SCONFIG, $_GET;
	$_SGLOBAL['id'] = 0;
	$_SGLOBAL['supe_username'] = 'Guest';
	session_start();
	$session = empty($_SESSION['auth'])?'':$_SESSION['auth'];
	@list($psw,$username,$id,$auidstr,$tid) = explode("\t", authcode($session, 'DECODE'));
	$uid = intval($id);
	$password = addslashes($psw);
	dbconnect(1);
	$manager = array("id"=>$uid,"mpsw"=>$password);
	adminchecke($manager);
}

//管理员登录检验
function adminchecke($manager) {
	global $_SGLOBAL;
	$member = array ();
	$query = $_SGLOBAL['db']->query("select * from ".tname('manager')." where id=".$manager['id']." and mpsw='".$manager['mpsw']."'");
	if ($tmanager = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL ['id'] = $tmanager ['id'];
		$_SGLOBAL ['roletype'] = $tmanager ['roletype'];
		$_SGLOBAL ['name'] = $tmanager ['mname'];
		$_SGLOBAL ['tid'] = $tmanager ['tid'];
		$_SGLOBAL ['authority'] = "";
		if (! empty ( $tmanager ['auidstr'] )) {
			if (strcmp ( md5 ( 'admin' ), $tmanager ['auidstr'] ) == 0) {
				$_SGLOBAL ['authority'] = $tmanager ['auidstr'];
			} else {
				$_SGLOBAL ['authority'] = $tmanager ['auidstr'];
			}
		}
	} else {
		//用户错误
		$_SGLOBAL ['id'] = 0;
		sclearcookie ();
	}
}

/**
 * 保存当前操作权限名称
 * Enter description here ...
 * @param String $curop
 */
function saveSession($curop){
	global $CurrentAuthOp;
	$sessionvalue = authcode ( "$curop\t".md5($curop.':'.$CurrentAuthOp), 'ENCODE' );
	session_start();
	$_SESSION[$CurrentAuthOp] = $sessionvalue;
}
/**
 * 
 * 获取当前权限名并清除当前权限session
 * @param boolean $isClearSession <br>
 * false---不清除session  <br>
 * true----清除权限(系统默认清除当前权限$_SGLOBAL['currentAuth'])
 */
function getCurrentAuth($isClearSession=true){
	global $CurrentAuthOp,$_SGLOBAL;
	session_start();
	$session = empty($_SESSION[$CurrentAuthOp])?'':$_SESSION[$CurrentAuthOp];
	if($session){
		@list($curAut, $md5Auth) = explode("\t", authcode($session, 'DECODE'));
		$curAut = addslashes($curAut);
		$md5Auth = addslashes($md5Auth);
		//判断md5加密值是否相等
		$_SGLOBAL['currentAuth'] = '';
		if(strcmp(md5($curAut.'_'.$CurrentAuthOp), $md5Auth)){
			$_SGLOBAL['currentAuth'] = $curAut;
		}
	}
	/*清除当前权限的值*/
	if($isClearSession){
		clearAuthSession();
	}
}
/**
 * 
 * 
 * @param unknown_type $Dao
 * @param unknown_type $indexs
 */
function getAuthority($Dao,$indexs){
	return $Dao->getAuthority($indexs);
}
/**
 * 清除当前权限session
 * Enter description here ...
 */
function clearAuthSession(){
	global $CurrentAuthOp;
	session_start();
	$_SESSION[$CurrentAuthOp]='';
}

/*function getcookie($db=0) {
	global $_SGLOBAL, $cookiepre, $_SCONFIG, $_GET;

	$_SGLOBAL['supe_uid'] = 0;
	$_SGLOBAL['supe_username'] = 'Guest';
	$_SGLOBAL['member'] = array(
		'uid' => 0,
		'groupid' => 0,
		'username' => 'Guest',
		'password' => '',
		'secques' => '',
		'timeoffset' => $_SCONFIG['timeoffset'],
		'dateformat' => 'Y-m-d',
		'timeformat' => 'H:i:s',
		'havespace' => 0,
		'newpm' => 0
	);
	if($db == 2) {
		$cookie = empty($_COOKIE[$cookiepre.'sauth'])?'':$_COOKIE[$cookiepre.'sauth'];
		if(empty($cookie)) $cookie = empty($_COOKIE[$cookiepre.'auth'])?'':$_COOKIE[$cookiepre.'auth'];
	} else {
		$cookie = empty($_COOKIE[$cookiepre.'auth'])?'':$_COOKIE[$cookiepre.'auth'];
	}
	if($cookie) {
		@list($password, $secques, $uid) = explode("\t", authcode($cookie, 'DECODE'));
		$uid = intval($uid);
		$password = addslashes($password);
		$secques = addslashes($secques);

		$_SGLOBAL['supe_uid'] = $uid;
		if(empty($_SGLOBAL['supe_uid'])) sclearcookie();

		if($db && $_SGLOBAL['supe_uid'] && $password) {
			dbconnect(1);
			$member = array();
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('members').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND password=\''.$password.'\' AND secques=\''.$secques.'\'');
			if($member = $_SGLOBAL['db']->fetch_array($query)) {
				$_SGLOBAL['group']['groupid']=$member['groupid'];
			} else {
				//论坛查询
				$query = $_SGLOBAL['db_bbs']->query('SELECT * FROM '.tname('members', 1).' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND password=\''.$password.'\' AND secques=\''.$secques.'\'');
				if($member = $_SGLOBAL['db_bbs']->fetch_array($query)) {
					$insertsqlarr = array(
						'uid' => $member['uid'],
						'groupid' => $member['groupid'],
						'username' => addslashes($member['username']),
						'password' => addslashes($member['password']),
						'secques' => addslashes($member['secques']),
						'timeoffset' => addslashes($member['timeoffset']),
						'dateformat' => addslashes($member['dateformat']),
						'havespace' => 0
					);
					replacetable('members', $insertsqlarr);
				}
			}
			if(empty($member)) {
				//用户错误
				$_SGLOBAL['supe_uid'] = 0;
				sclearcookie();
			} else {
				$_SGLOBAL['supe_uid'] = $member['uid'];
				$_SGLOBAL['supe_username'] = $member['username'];
				$_SGLOBAL['member'] = $member;
				if(empty($member['timeoffset']) || $member['timeoffset'] == '9999') {
					$_SGLOBAL['member']['timeoffset'] = $_SCONFIG['timeoffset'];
				}
				if(empty($member['dateformat'])) $_SGLOBAL['member']['dateformat'] = 'Y-m-d';
				if($member['timeformat']) {
					$_SGLOBAL['member']['timeformat'] = 'h:i:s A';
				} else {
					$_SGLOBAL['member']['timeformat'] = 'H:i:s';
				}
			}
		}
	} elseif (!empty($_COOKIE[$cookiepre.'sid'])) {
		//sessions
		dbconnect(1);
		$sid = addslashes($_COOKIE[$cookiepre.'sid']);
		$query = $_SGLOBAL['db_bbs']->query("SELECT * FROM ".tname('sessions', 1)." WHERE sid='$sid'");
		if($member = $_SGLOBAL['db_bbs']->fetch_array($query)) {
			$member['havespace'] = 0;
			$member['timeoffset'] = $_SCONFIG['timeoffset'];
			$_SGLOBAL['supe_uid'] = $member['uid'];
			$_SGLOBAL['supe_username'] = $member['username'];
			$_SGLOBAL['member'] = $member;
		}
	}
	if(empty($_SGLOBAL['member']['timeoffset'])) $_SGLOBAL['member']['timeoffset'] = $_SCONFIG['timeoffset'];
	
	//用户组
	@include_once(S_ROOT.'./data/system/group.cache.php');
	if(!empty($_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']])) {
		$_SGLOBAL['group'] = $_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']];
	}
	
	//用户名处理
	$_SGLOBAL['supe_username_show'] = $_SGLOBAL['supe_username'];
	$_SGLOBAL['supe_username'] = addslashes($_SGLOBAL['supe_username']);
}*/

function sclearcookie() {
	global $_SGLOBAL;

	ssetcookie('sid', '', -86400 * 365);
	ssetcookie('auth', '', -86400 * 365);
	ssetcookie('sauth', '', -86400 * 365);
}

function ssetcookie($var, $value, $life=0) {
	global $_SGLOBAL, $cookiepre, $cookiepath, $cookiedomain;
	
	setcookie($cookiepre.$var, $value, $life?$_SGLOBAL['timestamp']+$life:0, $cookiepath, $cookiedomain, $_SERVER['SERVER_PORT']==443?1:0);
}

function dbconnect($bbsmode=0) {
	global $_SGLOBAL;
	global $dbcharset, $dbhost, $dbuser, $dbpw, $dbname, $pconnect;
	global $dbcharset_bbs, $dbhost_bbs, $dbuser_bbs, $dbpw_bbs, $dbname_bbs, $pconnect_bbs;

	if(empty($_SGLOBAL['db'])) {
		include_once(S_ROOT.'./class/db_mysql.class.php');
		$_SGLOBAL['db'] = new dbstuff;
		$_SGLOBAL['db']->charset = $dbcharset;
		$_SGLOBAL['db']->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		unset($dbuser, $dbpw);
	}
	if(!empty($bbsmode)) {
		if(empty($dbhost_bbs) || $dbhost == $dbhost_bbs) {
			//同一台服务器
			$_SGLOBAL['db_bbs'] = $_SGLOBAL['db'];
		} else {
			//不同的mysql服务器
			if(empty($_SGLOBAL['db_bbs'])) {
				include_once(S_ROOT.'./class/db_mysql.class.php');
				$_SGLOBAL['db_bbs'] = new dbstuff;
				$_SGLOBAL['db_bbs']->charset = $dbcharset_bbs;
				$_SGLOBAL['db_bbs']->connect($dbhost_bbs, $dbuser_bbs, $dbpw_bbs, $dbname_bbs, $pconnect_bbs);
				unset($dbuser_bbs, $dbpw_bbs);
			}			
		}
	}
}

function stripsearchkey($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = stripsearchkey($val);
		}
	} else {
		$string = trim($string);
		$string = str_replace('*', '%', addcslashes($string, '%_'));
		$string = str_replace('_', '\_', $string);
	}
	return $string;
}

function postget($var) {
	$value = '';
	if(isset($_POST[$var])) {
		$value = $_POST[$var];
	} elseif (isset($_GET[$var])) {
		$value = $_GET[$var];
	}
	return $value;
}

function obclean() {
	global $_SCONFIG;

	ob_end_clean();
	if ($_SCONFIG['gzipcompress'] && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

function showxml($text, $title='') {
	global $_SCONFIG, $lang;

	if(!empty($title)) {
		$text = '<h5><a href="javascript:;" onclick="document.getElementById(\'xspace-ajax-div\').style.display=\'none\';">'.$lang['close'].'</a>'.$title.'</h5><div class="xspace-ajaxcontent">'.$text.'</div>';
	}
	obclean();
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	header("Content-type: application/xml");
	echo "<?xml version=\"1.0\" encoding=\"$_SCONFIG[charset]\"?>\n";
	echo "<root><![CDATA[";
	echo $text;
	echo "]]></root>";
	exit;
}

function messagebox($type, $message, $url='') {
	global $_SCONFIG, $charset;
	obclean();
	
	include_once(S_ROOT.'./language/message.lang.php');

	$siteurl = S_URL;
	
	if(!empty($mlang[$message])) $message = $mlang[$message];
	if($type == 'ok') {
		$class = '';
	} else {
		$class = ' class="warning"';
	}
	if($url) {
		$metarefresh = '<meta http-equiv="refresh" content="2;url='.$url.'" />';
		$urlstr = '<a href="'.$url.'" class="done">'.$mlang['confirm'].'</a>';
	} else {
		$metarefresh = '';
		$urlstr = '<a href="javascript:window.close();" class="done">'.$mlang['close'].'</a>';
	}

	print <<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
	$metarefresh
	<title>$mlang[title] - Powered By SupeSite</title>
	<link rel="stylesheet" type="text/css" href="$siteurl/templates/$_SCONFIG[template]/css/style.css" />
	<script type="text/javascript">
	var siteUrl = "$siteurl";
	</script>
	<script src="$siteurl/include/js/ajax.js" type="text/javascript" language="javascript"></script>
	<script src="$siteurl/include/js/common.js" type="text/javascript" language="javascript"></script>
	</head>
	<body>
	<table summary="" id="notice" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td id="notice_message"$class>$message</td>
		</tr>
		<tr>
			<td id="notice_links">
				$urlstr
				<a href="javascript:history.back();" class="back">$mlang[back]</a>
				<a href="$siteurl/" class="home">$_SCONFIG[sitename]</a>
			</td>
		</tr>
	</table>
	</body>
	</html>
END;
exit;
}

function secho($array, $eixt=1) {
	if(is_array($array)) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	} else {
		echo '<br>';
		echo shtmlspecialchars($array);
		echo '<br>';
	}
	if($eixt) exit();
}

function freshcookie($itemid) {
	global $cookiepre, $_SGLOBAL;

	$isupdate = 1;
	$old = empty($_COOKIE[$cookiepre.'supe_refresh_items'])?0:trim($_COOKIE[$cookiepre.'supe_refresh_items']);
	$itemidarr = explode('_', $old);
	if(in_array($itemid, $itemidarr)) {
		$isupdate = 0;
	} else {
		$itemidarr[] = trim($itemid);
		ssetcookie('supe_refresh_items', implode('_', $itemidarr));
	}
	if(empty($_COOKIE)) $isupdate = 0;

	return $isupdate;
}

function submitcheck($var, $checksec=0) {
	global $cookiepre, $_POST, $_SGLOBAL, $_SCONFIG;

	if(empty($_POST[$var])) {
		return false;
	} else {
		//if($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))) {
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if(empty($_SCONFIG['noseccode']) && $checksec) {
				if(!empty($_COOKIE[$cookiepre.'supe_hash'])) {
					$supe_hash = authcode($_COOKIE[$cookiepre.'supe_hash'], 'DECODE');
					$sarr = explode("\t", $supe_hash);
					empty($sarr[0])?$sectime=0:$sectime=intval($sarr[0]);
					empty($sarr[1])?$seccode=0:$seccode=intval($sarr[1]);
					$seccode = sprintf('%04d', $seccode);
					if($seccode && $seccode == $_POST['seccode'] && ($_SGLOBAL['timestamp'] - $sectime < 900)) {//15min
						ssetcookie('supe_hash', 0);
						return true;
					}
				}
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}

function getuserfile($uid, $type, $mode='path', $mkdir=0) {

	$idvalue = ($uid>9)?substr($uid, -2, 2):$uid;
	$userdir = S_ROOT.'./user/'.$idvalue;
	if($mkdir) {
		if(!is_dir($userdir)) if(!@mkdir($userdir)) exit('Error, php can\'t make dir: '.$userdir);
	}

	switch ($type) {
		case 'css': $filename = 'css_'.$uid.'.cache.css'; break;
		case 'jpg': $filename = 'photo_'.$uid.'.jpg'; break;
		case 'gif': $filename = 'photo_'.$uid.'.gif'; break;
		case 'html': $filename = 'html_'.$uid.'.cache.php'; break;
		case 'diy': $filename = 'diy_css_'.$uid.'.cache.css'; break;
		case 'pdf': $filename = 'corpus_'.$uid.'.pdf'; break;
		case 'xml': $filename = 'corpus_'.$uid.'.xml'; break;
		default: $filename = $uid.'.data'; break;
	}
	if($mode == 'url') {
		return S_URL.'/user/'.$idvalue.'/'.$filename;
	} else {
		return $userdir.'/'.$filename;
	}
}

function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}

function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function sheader($url){
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $url");
	exit();
}

function replacetable($tablename, $insertsqlarr) {
	global $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.$insert_key;
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	$_SGLOBAL['db']->query('REPLACE INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.') ');
}

//简单跳转的函数
function jumpurl($url, $time=1000, $mode='js') {
	if($mode == 'js') {
		echo "<script>
			function redirect() {
				window.location.replace('$url');
			}
			setTimeout('redirect();', $time);
			</script>";
	} else {
		$time = $time/1000;
		echo "<html><head><title></title><meta http-equiv=\"refresh\" content=\"$time;url=$url\"></head><body></body></html>";
	}
	exit;
}

//获取文件名后缀
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

//获取频道信息
function getchannels() {
	global $_SGLOBAL, $_SCONFIG, $lang;
	
	$channels = array('default'=>'', 'menus'=>array(), 'types'=>array());

	$_SGLOBAL['type'] = array();
	foreach ($_SCONFIG['channel'] as $value) {
		
		//默认频道文件
		if(!empty($_SCONFIG['defaultchannel']) && $value['nameid'] == $_SCONFIG['defaultchannel']) {
			if($value['type'] == 'user') {
				$channels['default'] = 'channel/channel_'.$value['nameid'].'.php';//默认频道
			} elseif($value['nameid'] != 'index') {
				$channels['default'] = $value['nameid'].'.php';
			}
		}
		
		//处理默认链接和名称
		if(empty($value['url'])) {
			if($value['type'] == 'user') {
				$value['url'] = geturl("action/channel/name/$value[nameid]");
			} else {
				$value['url'] = geturl("action/$value[nameid]");
			}
		}
		if(empty($value['name'])) $value['name'] = $lang[$value['nameid']];
		
		$channels['menus'][$value['nameid']] = $value;//全部频道
		
		//获取系统频道
		if($value['type'] == 'type') {
			$channels['types'][$value['nameid']] = $value;//系统频道
			$_SGLOBAL['type'][] = $value['nameid'];
		}
	}
	
	return $channels;
}

//根据类型获取表名
function gettypetablename($type) {
	if($type == 'goods' || $type == 'news') {
		return "space{$type}";
	} else {
		return "space{$type}s";
	}
}

//更新信息查看数
function updateviewnum($itemid) {
	global $_SGLOBAL;
	
	$logfile = S_ROOT.'./log/viewcount.log';
	if(@$fp = fopen($logfile, 'a+')) {
		fwrite($fp, $itemid."\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET viewnum=viewnum+1 WHERE itemid=\''.$itemid.'\'');
	}
}

//更新空间查看数
function updatespaceviewnum($uid) {
	global $_SGLOBAL;
	
	$logfile = S_ROOT.'./log/spaceviewcount.log';
	if(@$fp = fopen($logfile, 'a+')) {
		fwrite($fp, $uid."\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		$_SGLOBAL['db']->query('UPDATE '.tname('userspaces').' SET viewnum=viewnum+1 WHERE uid=\''.$uid.'\'');
	}
}


?>