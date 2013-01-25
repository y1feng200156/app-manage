<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	��ͬ����

	$RCSfile: common.func.php,v $
	$Revision: 1.203.4.11 $
	$Date: 2007/06/21 17:56:45 $
*/

if (! defined ( 'IN_SUPESITE' )) {
	exit ( 'Access Denied' );
}

/**
 * 
 * @param $key session��
 * @param $value sessionֵ
 * 
 */
function PUT_SESSION($key,$value){
	session_start();
	$_SESSION[$key] = $value;
}

/**
 * 
 * @param $key ����
 * 
 */
function GET_SESSION($key){
	session_start();
	return $_SESSION[$key];
}

/**
 * 
 * @param $WID ��·ID
 * @param $LASTTIME ʱ���
 * @return false timeout  else session is avillable
 */
function SESSION_TIMEOUT($WID,$LASTTIME){
	$id = GET_SESSION(SESSIONWAYKEY);
	$time = GET_SESSION(SESSIONWAYDATE);
	if(($WID - $id)==0 && ($time-$LASTTIME)==0){
		return true;
	} 
	return false;
}

/**
 * ��Ǯ��λת��
 * @param ��Ǯ����
 * @return һ������
 */
function unitUtil($price){
	//�˴��Զ���ת������
	$M = array("result"=>$price,"unit"=>"Ԫ");
	return $M;
}

//����ת�����ִ�
function arrayeval($array, $level = 0) {
	$space = '';
	for($i = 0; $i <= $level; $i ++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	foreach ( $array as $key => $val ) {
		$key = is_string ( $key ) ? '\'' . addcslashes ( $key, '\'\\' ) . '\'' : $key;
		$val = ! is_array ( $val ) && (! preg_match ( "/^\-?\d+$/", $val ) || strlen ( $val ) > 12) ? '\'' . addcslashes ( $val, '\'\\' ) . '\'' : $val;
		if (is_array ( $val )) {
			$evaluate .= "$comma$key => " . arrayeval ( $val, $level + 1 );
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",\n$space";
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

//���ļ�
function sreadfile($filename, $mode = 'r', $remote = 0, $maxsize = 0, $jumpnum = 0) {
	if ($jumpnum > 5)
		return '';
	$contents = '';
	
	if ($remote) {
		$httpstas = '';
		$urls = initurl ( $filename );
		if (empty ( $urls ['url'] ))
			return '';
		
		$fp = @fsockopen ( $urls ['host'], $urls ['port'], $errno, $errstr, 20 );
		if ($fp) {
			if (! empty ( $urls ['query'] )) {
				fputs ( $fp, "GET $urls[path]?$urls[query] HTTP/1.1\r\n" );
			} else {
				fputs ( $fp, "GET $urls[path] HTTP/1.1\r\n" );
			}
			fputs ( $fp, "Host: $urls[host]\r\n" );
			fputs ( $fp, "Accept: */*\r\n" );
			fputs ( $fp, "Referer: $urls[url]\r\n" );
			fputs ( $fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 5.00; Windows 98)\r\n" );
			fputs ( $fp, "Pragma: no-cache\r\n" );
			fputs ( $fp, "Cache-Control: no-cache\r\n" );
			fputs ( $fp, "Connection: Close\r\n\r\n" );
			
			$httpstas = explode ( " ", fgets ( $fp, 128 ) );
			if ($httpstas [1] == 302 || $httpstas [1] == 302) {
				$jumpurl = explode ( " ", fgets ( $fp, 128 ) );
				return sreadfile ( trim ( $jumpurl [1] ), 'r', 1, 0, ++ $jumpnum );
			} elseif ($httpstas [1] != 200) {
				fclose ( $fp );
				return '';
			}
			
			$length = 0;
			$size = 1024;
			while ( ! feof ( $fp ) ) {
				$line = trim ( fgets ( $fp, 128 ) );
				$size = $size + 128;
				if (empty ( $line ))
					break;
				if (strexists ( $line, 'Content-Length' )) {
					$length = intval ( trim ( str_replace ( 'Content-Length:', '', $line ) ) );
					if (! empty ( $maxsize ) && $length > $maxsize) {
						fclose ( $fp );
						return '';
					}
				}
				if (! empty ( $maxsize ) && $size > $maxsize) {
					fclose ( $fp );
					return '';
				}
			}
			fclose ( $fp );
			
			if (@$handle = fopen ( $urls ['url'], $mode )) {
				if (function_exists ( 'stream_get_contents' )) {
					$contents = stream_get_contents ( $handle );
				} else {
					$contents = '';
					while ( ! feof ( $handle ) ) {
						$contents .= fread ( $handle, 8192 );
					}
				}
				fclose ( $handle );
			} elseif (@$ch = curl_init ()) {
				curl_setopt ( $ch, CURLOPT_URL, $urls ['url'] );
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 ); //timeout
				$contents = curl_exec ( $ch );
				curl_close ( $ch );
			} else {
				//�޷�Զ���ϴ�
			}
		}
	} else {
		if (@$handle = fopen ( $filename, $mode )) {
			$contents = fread ( $handle, filesize ( $filename ) );
			fclose ( $handle );
		}
	}
	
	return $contents;
}

//д�ļ�
function writefile($filename, $writetext, $filemod = 'text', $openmod = 'w', $eixt = 1) {
	if (! @$fp = fopen ( $filename, $openmod )) {
		if ($eixt) {
			exit ( 'File :<br>' . srealpath ( $filename ) . '<br>Have no access to write!' );
		} else {
			return false;
		}
	} else {
		$text = '';
		if ($filemod == 'php') {
			$text = "<?php\r\n\r\nif(!defined('IN_SUPESITE')) exit('Access Denied');\r\n\r\n";
		}
		$text .= $writetext;
		if ($filemod == 'php') {
			$text .= "\r\n\r\n?>";
		}
		flock ( $fp, 2 );
		fwrite ( $fp, $text );
		fclose ( $fp );
		return true;
	}
}

function initurl($url) {
	
	$newurl = '';
	$blanks = array ('url' => '' );
	$urls = $blanks;
	
	if (strlen ( $url ) < 10)
		return $blanks;
	$urls = @parse_url ( $url );
	if (empty ( $urls ) || ! is_array ( $urls ))
		return $blanks;
	if (empty ( $urls ['scheme'] ))
		return $blanks;
	if ($urls ['scheme'] == 'file')
		return $blanks;
	
	if (empty ( $urls ['path'] ))
		$urls ['path'] = '/';
	$newurl .= $urls ['scheme'] . '://';
	$newurl .= empty ( $urls ['user'] ) ? '' : $urls ['user'];
	$newurl .= empty ( $urls ['pass'] ) ? '' : ':' . $urls ['pass'];
	$newurl .= empty ( $urls ['host'] ) ? '' : ((! empty ( $urls ['user'] ) || ! empty ( $urls ['pass'] )) ? '@' : '') . $urls ['host'];
	$newurl .= empty ( $urls ['port'] ) ? '' : ':' . $urls ['port'];
	$newurl .= empty ( $urls ['path'] ) ? '' : $urls ['path'];
	$newurl .= empty ( $urls ['query'] ) ? '' : '?' . $urls ['query'];
	$newurl .= empty ( $urls ['fragment'] ) ? '' : '#' . $urls ['fragment'];
	
	$urls ['port'] = empty ( $urls ['port'] ) ? '80' : $urls ['port'];
	$urls ['url'] = $newurl;
	
	return $urls;
}

/**
 * ����ģ���ļ�
 * @param unknown_type $tplfile	  php�ļ���
 * @param unknown_type $fullpath	php�����ļ�·�����粻�Ĭ��admin/page/
 */
function template($tplfile, $fullpath = 0) {
	global $_SCONFIG;
	if (empty ( $fullpath )) {
		$filename = 'templates/'.S_TEMPLE.'/' . $tplfile . '.html.php';
		$objfile = S_ROOT . './cache/tpl/tpl_' . $_SCONFIG ['template'] . '_' . $tplfile . '.php';
		$tplfile = S_ROOT . './' . $filename;
	} else {
		$objfile = 'templates/'.S_TEMPLE.'/' . $fullpath . $tplfile;
		$filename = $objfile . '.html.php';
		$objfile = str_replace ( '/', '_', $objfile );
		$objfile = S_ROOT . './cache/tpl/tpl_' . $objfile . '.php';
		$tplfile = S_ROOT . './' . $filename;
	}
	
	$tplrefresh = 1;
	if (file_exists ( $objfile )) {
		if (empty ( $_SCONFIG ['tplrefresh'] )) {
			$tplrefresh = 0;
		} else {
			if (@filemtime ( $tplfile ) <= @filemtime ( $objfile )) {
				$tplrefresh = 0;
			}
		}
	}
	
	if ($tplrefresh) {
		include_once (S_ROOT . './function/template.func.php');
		parse_template ( $tplfile, $objfile );
	}
	return $objfile;
}

/**
 * �ж�session��·��id�ڲ����Լ�·���Ƿ��ʱ��������תҳ���filter
 * @param unknown_type $saveTime
 */
function ISNULL_WAYID_FILTER($saveTime) {
	$id = GET_SESSION(SESSIONWAYKEY);
	$time = GET_SESSION(SESSIONWAYDATE);
	if(empty($id)){
		include template("noselectway");
		exit();
	}
	if($time-$saveTime!=0){
		include template("sessiontimeout");
		exit();
	}
}

/**
 * �ж�session��·��id�ڲ����Լ�·���Ƿ��ʱ��������תҳ���filter
 * @param unknown_type $saveTime
 */
function ISNULL_WAYID_FILTER_RETURN($saveTime) {
	$id = GET_SESSION(SESSIONWAYKEY);
	$time = GET_SESSION(SESSIONWAYDATE);
	if(empty($id)){
		return "1";
	}
	if($time-$saveTime!=0){
		return "2";
	}
	return false;
}

//��ʽ��·��
function srealpath($path) {
	$path = str_replace ( './', '', $path );
	if (DIRECTORY_SEPARATOR == '\\') {
		$path = str_replace ( '/', '\\', $path );
	} elseif (DIRECTORY_SEPARATOR == '/') {
		$path = str_replace ( '\\', '/', $path );
	}
	return $path;
}

//�����ݱ�ȡ��CACHE�ı���
function getcache($cachekey, $tablename) {
	global $_SGLOBAL, $_SBLOCK, $_SCONFIG;
	
	if ($_SCONFIG ['allowcache'] && ! empty ( $cachekey ) && empty ( $_SBLOCK [$cachekey] )) {
		if ($_SCONFIG ['cachemode'] == 'file') {
			$cachefile = S_ROOT . './cache/block/' . substr ( $cachekey, 0, 1 ) . '/' . $cachekey . '.cache.data';
			if (file_exists ( $cachefile )) {
				if (@$fp = fopen ( $cachefile, 'r' )) {
					$data = fread ( $fp, filesize ( $cachefile ) );
					fclose ( $fp );
				}
				$_SBLOCK [$cachekey] ['value'] = $data;
				$_SBLOCK [$cachekey] ['filemtime'] = filemtime ( $cachefile );
			}
		} else {
			if (! isset ( $_SGLOBAL ['mkcachetables'] ))
				$_SGLOBAL ['mkcachetables'] = array ();
			$thetable = tname ( $tablename . '_' . substr ( $cachekey, 0, 1 ) );
			if ($query = $_SGLOBAL ['db']->query ( 'SELECT * FROM ' . $thetable . ' WHERE cachekey = \'' . $cachekey . '\'', 'SILENT' )) {
				while ( $result = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
					$_SBLOCK [$result ['cachekey']] ['value'] = $result ['value'];
					$_SBLOCK [$result ['cachekey']] ['updatetime'] = $result ['updatetime'];
				}
			} else {
				$_SGLOBAL ['mkcachetables'] [] = $thetable;
			}
		}
	}
}

//ģ��
function block($thekey, $param) {
	global $_SGLOBAL, $_SBLOCK, $_SCONFIG, $_SGET, $lang;
	
	$_SBLOCK [$thekey] = array ();
	$havethekey = false;
	$needcache = 0;
	
	//����key
	$cachekey = smd5 ( $thekey . $param );
	
	$paramarr = parseparameter ( $param, 0 );
	if (! empty ( $paramarr ['uid'] )) {
		$uid = $paramarr ['uid'];
	} elseif (! empty ( $paramarr ['authorid'] )) {
		$uid = $paramarr ['authorid'];
	} else {
		$uid = 0;
	}
	
	if (! empty ( $paramarr ['cachetime'] )) {
		if (! empty ( $paramarr ['perpage'] ) && ! empty ( $_SGET ['page'] )) {
			//��ҳ
			$cachekey = smd5 ( $thekey . $param . $_SGET ['page'] );
		}
		$cacheupdatetime = $paramarr ['cachetime'];
	} else {
		$cacheupdatetime = 0;
		$needcache = 3; //DO NOT CACHE
	}
	
	if ($cacheupdatetime) {
		//��ȡ����
		$tablename = ($thekey == 'spacetag') ? 'tagcache' : 'cache';
		getcache ( $cachekey, $tablename );
		
		if (! isset ( $_SBLOCK [$cachekey] )) {
			$needcache = 1; //û�л���
		} else {
			//�����´θ���ʱ��
			if (! empty ( $_SBLOCK [$cachekey] ['filemtime'] ))
				$_SBLOCK [$cachekey] ['updatetime'] = $_SBLOCK [$cachekey] ['filemtime'] + $cacheupdatetime;
			if ($_SBLOCK [$cachekey] ['updatetime'] < $_SGLOBAL ['timestamp']) {
				$needcache = 2; //��Ҫ����
			}
		}
	}
	
	if ($needcache) {
		$theblockarr = array ();
		
		include (S_ROOT . './include/block/block_' . $thekey . '_query.inc.php');
		
		$_SBLOCK [$thekey] = $theblockarr;
		$havethekey = true;
		$_SBLOCK [$cachekey] ['value'] = serialize ( $theblockarr );
		$_SBLOCK [$cachekey] ['updatetime'] = $_SGLOBAL ['timestamp'] + $cacheupdatetime;
		
		if ($needcache == 1 || $needcache == 2) {
			//INSERT-UPDATE
			$_SGLOBAL ['tpl_blockvalue'] [] = array ('cachekey' => $cachekey, 'uid' => $uid, 'cachename' => $thekey, 'value' => $_SBLOCK [$cachekey] ['value'], 'updatetime' => $_SBLOCK [$cachekey] ['updatetime'] );
		}
	}
	
	if (! $havethekey) {
		if (! empty ( $_SBLOCK [$cachekey] ['value'] )) {
			$_SBLOCK [$thekey] = unserialize ( $_SBLOCK [$cachekey] ['value'] );
		} else {
			$_SBLOCK [$thekey] = array ();
		}
	}
	
	$iarr = $_SBLOCK [$thekey];
	if (! empty ( $paramarr ['cachename'] )) {
		if (empty ( $_SBLOCK [$thekey] ['multipage'] )) {
			$_SBLOCK [$paramarr ['cachename'] . '_multipage'] = '';
		} else {
			$_SBLOCK [$paramarr ['cachename'] . '_multipage'] = $_SBLOCK [$thekey] ['multipage'];
		}
		$_SBLOCK [$paramarr ['cachename']] = $_SBLOCK [$thekey];
		unset ( $_SBLOCK [$paramarr ['cachename']] ['multipage'] );
	}
	
	if ($paramarr ['tpl'] != 'data') {
		$paramarr ['tpl'] = 'styles/' . $paramarr ['tpl'] . '.html.php';
		include template ( $paramarr ['tpl'], 1 );
	}
}

//����ָ�����е�cachekey
function maketplblockvalue($tablename = 'cache') {
	global $_SGLOBAL, $_SCONFIG;
	
	if ($_SCONFIG ['allowcache'] && ! empty ( $_SGLOBAL ['tpl_blockvalue'] )) {
		if ($_SCONFIG ['cachemode'] == 'file') {
			//�ı��洢
			foreach ( $_SGLOBAL ['tpl_blockvalue'] as $tplvalue ) {
				$cachedir = S_ROOT . './cache/block/' . substr ( $tplvalue ['cachekey'], 0, 1 );
				$dircheck = false;
				if (! is_dir ( $cachedir )) {
					if (@mkdir ( $cachedir )) {
						$dircheck = true;
					}
				} else {
					$dircheck = true;
				}
				if ($dircheck) {
					$cachefile = $cachedir . '/' . $tplvalue ['cachekey'] . '.cache.data';
					if (@$fp = fopen ( $cachefile, 'w' )) {
						fwrite ( $fp, $tplvalue ['value'] );
						fclose ( $fp );
					}
				}
			}
		} else {
			//����ֱ�
			if (! empty ( $_SGLOBAL ['mkcachetables'] )) {
				$basetable = tname ( $tablename );
				$query = $_SGLOBAL ['db']->query ( "SHOW CREATE TABLE $basetable" );
				$creattable = $_SGLOBAL ['db']->fetch_array ( $query );
				foreach ( $_SGLOBAL ['mkcachetables'] as $thetable ) {
					$sql = str_replace ( $basetable, $thetable, $creattable ['Create Table'] );
					$_SGLOBAL ['db']->query ( $sql, 'SILENT' ); //�����ֱ�
				}
			}
			$insertsqls = array ();
			$thetables = array ();
			foreach ( $_SGLOBAL ['tpl_blockvalue'] as $tplvalue ) {
				$thetable = tname ( $tablename . '_' . substr ( $tplvalue ['cachekey'], 0, 1 ) );
				$thetables [] = $thetable;
				$insertsqls [$thetable] [] = '(\'' . addslashes ( $tplvalue ['cachekey'] ) . '\', \'' . $tplvalue ['uid'] . '\', \'' . addslashes ( $tplvalue ['cachename'] ) . '\', \'' . addslashes ( $tplvalue ['value'] ) . '\', \'' . $tplvalue ['updatetime'] . '\')';
			}
			foreach ( $thetables as $thetable ) {
				$_SGLOBAL ['db']->query ( 'REPLACE INTO ' . $thetable . ' (cachekey, uid, cachename, value, updatetime) VALUES ' . implode ( ',', $insertsqls [$thetable] ) );
			}
		}
	}
}

function smd5($str) {
	return substr ( md5 ( $str ), 8, 16 );
}

function snl2br($message) {
	return nl2br ( str_replace ( array ("\t", '   ', '  ' ), array ('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;' ), $message ) );
}

//�滻�ַ����е������ַ�
//ȥ��ָ���ַ�����\\��\'ǰ��\
function sstripslashes($string) {
	
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = sstripslashes ( $val );
		}
	} else {
		$string = stripslashes ( $string );
	}
	return $string;
}

function stripbbcode($string) {
	return preg_replace ( "/\[.+?\]/i", '', $string );
}

//�õ�����BBS��URL��·��,��$para�еļ����ͼ�ֵ��Ϊ��������
function getbbsurl($scriptname, $para = array()) {
	$str = '';
	$comma = '?';
	if (is_array ( $para ) && $para) {
		foreach ( $para as $key => $value ) {
			$str .= $comma . $key . '=' . rawurlencode ( $value );
			$comma = '&';
		}
	}
	$scriptname .= $str;
	
	return B_URL . '/' . $scriptname;
}

function selecttable($tablename, $selectsqlarr, $wheresqlarr, $plussql = '') {
	global $_SGLOBAL;
	
	$selectsql = $comma = '';
	if (count ( $selectsqlarr )) {
		foreach ( $selectsqlarr as $select_key => $select_value ) {
			$selectsql .= $comma . $select_value;
			$comma = ', ';
		}
	} else {
		$selectsql = '*';
	}
	
	$results = array ();
	$query = $_SGLOBAL ['db']->query ( 'SELECT ' . $selectsql . ' FROM ' . tname ( $tablename ) . ' WHERE ' . getwheresql ( $wheresqlarr ) . ' ' . $plussql );
	while ( $r_array = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		$results [] = $r_array;
	}
	return $results;
}

function selecttable_nowhere($tablename, $selectsqlarr, $wheresqlarr, $plussql = '') {
	global $_SGLOBAL;
	
	$selectsql = $comma = '';
	if (count ( $selectsqlarr )) {
		foreach ( $selectsqlarr as $select_key => $select_value ) {
			$selectsql .= $comma . $select_value;
			$comma = ', ';
		}
	} else {
		$selectsql = '*';
	}
	
	$results = array ();
	$query = $_SGLOBAL ['db']->query ( 'SELECT ' . $selectsql . ' FROM ' . tname ( $tablename ) . '  ' . $wheresqlarr . ' ' . $plussql );
	while ( $r_array = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		$results [] = $r_array;
	}
	return $results;
}

function inserttable($tablename, $insertsqlarr, $returnid = 0) {
	global $_SGLOBAL;
	
	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ( $insertsqlarr as $insert_key => $insert_value ) {
		$insertkeysql .= $comma . $insert_key;
		$insertvaluesql .= $comma . '\'' . $insert_value . '\'';
		$comma = ', ';
	}
	$query = $_SGLOBAL ['db']->query ( 'INSERT INTO ' . tname ( $tablename ) . ' (' . $insertkeysql . ') VALUES (' . $insertvaluesql . ') ' );
	if ($returnid) {
		$id = $_SGLOBAL ['db']->insert_id ();
		return $id;
	}
	return $query;
}

function inserttableTransaction($tablename, $insertsqlarr, $returnid = 0) {
	global $_SGLOBAL;
	
	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ( $insertsqlarr as $insert_key => $insert_value ) {
		$insertkeysql .= $comma . $insert_key;
		$insertvaluesql .= $comma . '\'' . $insert_value . '\'';
		$comma = ', ';
	}
	$query = $_SGLOBAL ['db']->queryTransaction ( 'INSERT INTO ' . tname ( $tablename ) . ' (' . $insertkeysql . ') VALUES (' . $insertvaluesql . ') ' );
	if ($returnid) {
		$id = $_SGLOBAL ['db']->insert_id ();
		return $id;
	}
	return $query;
}

/**
 * 
 * ����в�������
 * @param String $tablename ����
 * @param Array $insertsqlarr �������ݼ�ֵ��($key=>$value $keyΪ���ݿ��ֶΣ�$value�Ƕ�Ӧ�ֶε�ֵ)
 * @param unknown_type $returnid �������ɹ��򷵻ز�������������ʧ���򷵻�Ϊfalse
 */
function insertsql($tablename, $insertsqlarr, $returnid = 0) {
	global $_SGLOBAL;
	
	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ( $insertsqlarr as $insert_key => $insert_value ) {
		$insertkeysql .= $comma . $insert_key;
		$insertvaluesql .= $comma . '\'' . $insert_value . '\'';
		$comma = ', ';
	}
	$query = $_SGLOBAL ['db']->query ( 'INSERT INTO ' . tname ( $tablename ) . ' (' . $insertkeysql . ') VALUES (' . $insertvaluesql . ') ' );
	
	if(!$query){
		return false;
	}
	if ($returnid) {
		$id = $_SGLOBAL ['db']->insert_id ();
		return $id;
	}
}

/**
 * 
 * ��ʹ������������ݿ����ʱʹ��
 * @param unknown_type $tablename ����
 * @param unknown_type $insertsqlarr �ֶζ�Ӧ��valueֵ����($key=>$value)
 * @return return true excute success,or false which excute falier
 * @author ���Ͼ�
 */
function insertTransacation($tablename, $insertsqlarr) {
	global $_SGLOBAL;
	
	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ( $insertsqlarr as $insert_key => $insert_value ) {
		$insertkeysql .= $comma . $insert_key;
		$insertvaluesql .= $comma . '\'' . $insert_value . '\'';
		$comma = ', ';
	}
	return $_SGLOBAL ['db']->queryTransaction ( 'INSERT INTO ' . tname ( $tablename ) . ' (' . $insertkeysql . ') VALUES (' . $insertvaluesql . ') ' );
}

function insertTranBatch($tablename, $insertsqlarr){
	global $_SGLOBAL;
	$insertkeysql = $insertvaluesql = $comma = '';
	$insertkey = implode(",",array_keys($insertsqlarr[0]));
	//��ȡ��ֵ
	array_walk($insertsqlarr, "translateTablevalue");
	$insertvaluesql = implode(",", $insertsqlarr);
	return $_SGLOBAL ['db']->queryTransaction ( 'INSERT INTO ' . tname ( $tablename ) . ' (' . $insertkey . ') VALUES ' . $insertvaluesql . ' ' );
}

/**
 * ����ά����ת����sqlһά����
 * Enter description here ...
 * @param unknown_type $value
 */
function translateTablevalue(&$value){
	$insertvaluesql = $comma = '';
	foreach ( $value as $insert_key => $insert_value ) {
		$insertvaluesql .= $comma . '\'' . $insert_value . '\'';
		$comma = ', ';
	}
	$value = '('.$insertvaluesql.")";
}

/**
 * 
 * Enter description here ...
 * @param unknown_type $tablename
 * @param unknown_type $wheresqlarr
 */
function delTrasanction($tablename, $wheresqlarr) {
	global $_SGLOBAL;
	if (empty ( $wheresqlarr )) {
		$query = $_SGLOBAL ['db']->queryTransaction ( 'TRUNCATE TABLE ' . tname ( $tablename ) );
	} else {
		$query = $_SGLOBAL ['db']->queryTransaction ( 'DELETE FROM ' . tname ( $tablename ) . ' WHERE ' . getwheresql ( $wheresqlarr ) );
	}
	return $query;
}

function deletetable($tablename, $wheresqlarr) {
	global $_SGLOBAL;
	
	if (empty ( $wheresqlarr )) {
		$_SGLOBAL ['db']->query ( 'TRUNCATE TABLE ' . tname ( $tablename ) );
	} else {
		$_SGLOBAL ['db']->query ( 'DELETE FROM ' . tname ( $tablename ) . ' WHERE ' . getwheresql ( $wheresqlarr ) );
	}
}

/**
 * 
 * ʹ�������������ɾ��
 * @param unknown_type $tablename ����
 * @param unknown_type $wheresqlarr ����
 * @return return true if  excute sql success or failer 
 * @author ���Ͼ�
 */
function deleteTrasaction($tablename, $wheresqlarr) {
	global $_SGLOBAL;
	if (empty ( $wheresqlarr )) {
		$query = $_SGLOBAL ['db']->queryTransaction ( 'TRUNCATE TABLE ' . tname ( $tablename ) );
	} else {
		$query = $_SGLOBAL ['db']->queryTransaction ( 'DELETE FROM ' . tname ( $tablename ) . ' WHERE ' . getwheresql ( $wheresqlarr ) );
	}
	return $query;
}

function updatetable($tablename, $setsqlarr, $wheresqlarr) {
	global $_SGLOBAL;
	
	$setsql = $comma = '';
	foreach ( $setsqlarr as $set_key => $set_value ) {
		$setsql .= $comma . $set_key . '=\'' . $set_value . '\'';
		$comma = ', ';
	}
//	echo 'UPDATE ' . tname ( $tablename ) . ' SET ' . $setsql . ' WHERE ' . getwheresql ( $wheresqlarr );
	$_SGLOBAL ['db']->query ( 'UPDATE ' . tname ( $tablename ) . ' SET ' . $setsql . ' WHERE ' . getwheresql ( $wheresqlarr ) );
}
/**
 * 
 * ��ʹ������������ݸ��µ�ʱ��ʹ��
 * @param unknown_type $tablename table name
 * @param unknown_type $setsqlarr update sql
 * @param unknown_type $wheresqlarr It update relay the 'wheresqlarr' condition
 * @return return true means to excute success , or false 
 */
function updateTransaction($tablename, $setsqlarr, $wheresqlarr) {
	global $_SGLOBAL;
	
	$setsql = $comma = '';
	foreach ( $setsqlarr as $set_key => $set_value ) {
		$setsql .= $comma . $set_key . '=\'' . $set_value . '\'';
		$comma = ', ';
	}
	return $_SGLOBAL ['db']->queryTransaction ( 'UPDATE ' . tname ( $tablename ) . ' SET ' . $setsql . ' WHERE ' . getwheresql ( $wheresqlarr ) );
}
function getwheresql($wheresqlarr) {
	$result = $comma = '';
	if (empty ( $wheresqlarr )) {
		$result = '1';
	} elseif (is_array ( $wheresqlarr )) {
		foreach ( $wheresqlarr as $key => $value ) {
			$result .= $comma . $key . '=\'' . $value . '\'';
			$comma = ' AND ';
		}
	} else {
		$result = $wheresqlarr;
	}
	return $result;
}

/**
 * 
 * ���ַ������߼���html�����ַ�
 * @param unknown_type $char Ҫ��ӵ��ַ�
 * @param unknown_type $str ��������ַ���
 */
function addSpecialChar($char,$str){
	$special = htmlspecialchars($char).$str.htmlspecialchars($char);
	return $special;
}

//���ܺ���
function passport_encrypt($txt, $key) {
	srand ( ( double ) microtime () * 1000000 );
	$encrypt_key = md5 ( rand ( 0, 32000 ) );
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen ( $txt ); $i ++) {
		$ctr = $ctr == strlen ( $encrypt_key ) ? 0 : $ctr;
		$tmp .= $encrypt_key [$ctr] . ($txt [$i] ^ $encrypt_key [$ctr ++]);
	}
	return base64_encode ( passport_key ( $tmp, $key ) );
}

//���ܺ���
function passport_decrypt($txt, $key) {
	$txt = passport_key ( base64_decode ( rawurldecode ( $txt ) ), $key );
	$tmp = '';
	for($i = 0; $i < strlen ( $txt ); $i ++) {
		@$tmp .= $txt [$i] ^ $txt [++ $i];
	}
	return $tmp;
}
//����תӢ��
function CN_TO_GBK(&$value){
	 if(preg_match('/[\x80-\xff]./', $value)){
	 	$value = iconv("UTF-8", "GBK", $value);
	 }
}
//���ܺ���
function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5 ( $encrypt_key );
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen ( $txt ); $i ++) {
		$ctr = $ctr == strlen ( $encrypt_key ) ? 0 : $ctr;
		$tmp .= $txt [$i] ^ $encrypt_key [$ctr ++];
	}
	return $tmp;
}



//��ʽ����С����,�����ֽ����Զ���ʾ��'KB','MB'�ȵ�
function formatsize($size, $prec = 3) {
	$size = round ( abs ( $size ) );
	$units = array (0 => " B ", 1 => " KB", 2 => " MB", 3 => " GB", 4 => " TB" );
	if ($size == 0)
		return str_repeat ( " ", $prec ) . "$units[0]";
	$unit = min ( 4, floor ( log ( $size ) / log ( 2 ) / 10 ) );
	$size = $size * pow ( 2, - 10 * $unit );
	$digi = $prec - 1 - floor ( log ( $size ) / log ( 10 ) );
	$size = round ( $size * pow ( 10, $digi ) ) * pow ( 10, - $digi );
	//while (strlen($size)<=$prec) $size = " $size";
	return $size . $units [$unit];
}

//д������־����
function errorlog($type, $message, $halt = 0) {
	global $_SGLOBAL;
	@$fp = fopen ( S_ROOT . './log/errorlog.php', 'a' );
	@fwrite ( $fp, "<?exit?>$_SGLOBAL[timestamp]\t$type\t$_SGLOBAL[supe_uid]\t" . str_replace ( array ("\r", "\n" ), array (' ', ' ' ), trim ( shtmlspecialchars ( $message ) ) ) . "\n" );
	@fclose ( $fp );
	if ($halt) {
		exit ();
	}
}

//������Ϣ,��ʾ���̴���ʱ��
function debuginfo($echo = 1) {
	global $_SGLOBAL, $_SCONFIG;
	
	$info = '';
	if ($_SCONFIG ['debug']) {
		$mtime = explode ( ' ', microtime () );
		$totaltime = number_format ( ($mtime [1] + $mtime [0] - $_SGLOBAL ['supe_starttime']), 6 );
		$info .= 'Processed in ' . $totaltime . ' second(s), ' . $_SGLOBAL ['db']->querynum . ' queries' . ($_SCONFIG ['gzipcompress'] ? ', Gzip enabled' : NULL);
		$info .= '<br />';
	}
	if (! empty ( $_SCONFIG ['miibeian'] )) {
		$info .= '<a href="http://www.miibeian.gov.cn" target="_blank">' . $_SCONFIG ['miibeian'] . '</a><br />';
	}
	if ($echo) {
		echo $info;
	} else {
		return $info;
	}
}

function jsstrip($message) {
	$message = addcslashes ( $message, '/"\\' );
	$message = preg_replace ( "/([\r\n]+)/i", '\n', $message );
	return trim ( $message );
}

function cuthtml($string, $length, $havedot = 0) {
	$searcharr = array ("/\<img(.+?)\>/is", "/\<br.*?\>/is", "/\<p\>(.*?)\<\/p\>/is" );
	$replacearr = array ("[img\\1]", "[br]", "[p]\\1[/p]" );
	$string = preg_replace ( $searcharr, $replacearr, $string );
	$string = strip_tags ( $string );
	$searcharr = array ("/\[img(.+?)\]/s", "/\[br\]/", "/\[p\](.+?)\[\/p\]/s" );
	$replacearr = array ("<img\\1>", "<br />", "<p>\\1</p>" );
	$string = preg_replace ( $searcharr, $replacearr, $string );
	return trim ( cutstr ( $string, $length, $havedot ) );
}

function cutstr($string, $length, $havedot = 0) {
	global $_SCONFIG;
	
	//�жϳ���
	if (strlen ( $string ) <= $length) {
		return $string;
	}
	
	$wordscut = '';
	if (strtolower ( $_SCONFIG ['charset'] ) == 'utf-8') {
		//utf8����
		$n = 0;
		$tn = 0;
		$noc = 0;
		while ( $n < strlen ( $string ) ) {
			$t = ord ( $string [$n] );
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n ++;
				$noc ++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif (224 <= $t && $t < 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n ++;
			}
			if ($noc >= $length) {
				break;
			}
		}
		if ($noc > $length) {
			$n -= $tn;
		}
		$wordscut = substr ( $string, 0, $n );
	} else {
		for($i = 0; $i < $length - 3; $i ++) {
			if (ord ( $string [$i] ) > 127) {
				$wordscut .= $string [$i] . $string [$i + 1];
				$i ++;
			} else {
				$wordscut .= $string [$i];
			}
		}
	}
	//ʡ�Ժ�
	if ($havedot) {
		return $wordscut . '...';
	} else {
		return $wordscut;
	}
}

/**
 * ���ɷ�ҳURL��ַ����
 * 
 * @param int $num����¼����
 * @param int $perpage�� ÿҳ��¼��
 * @param int $curpage�� ��ǰ��ҳ��
 * @param array $mpurl�� URL��������
 * @return HTML ���ط�ҳHTML����
 */
function multi($num, $perpage, $curpage, $mpurl, $phpurl = 1) {
	
	global $_SHTML;
	
	if (($curpage - 1) * $perpage > $num)
		messagebox ( 'error', 'start_listcount_error' );
	
	$maxpages = 999999999;
	$multipage = $a_name = '';
	if ($phpurl) {
		$mpurl .= strpos ( $mpurl, '?' ) ? '&' : '?';
	} else {
		$urlarr = $mpurl;
		unset ( $urlarr ['php'] );
		unset ( $urlarr ['modified'] );
	}
	if ($num > $perpage) {
		$page = 10;
		$offset = 2;
		$realpages = @ceil ( $num / $perpage );
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
		if ($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $curpage + $page - $offset - 1;
			if ($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if (($to - $from) < $page && ($to - $from) < $pages) {
					$to = $page;
				}
			} elseif ($to > $pages) {
				$from = $curpage - $pages + $to;
				$to = $pages;
				if (($to - $from) < $page && ($to - $from) < $pages) {
					$from = $pages - $page + 1;
				}
			}
		}
		
		if ($phpurl) {
			$url = $mpurl . 'page=1' . $a_name;
			$url2 = $mpurl . 'page=' . ($curpage - 1) . $a_name;
		} else {
			$urlarr ['page'] = 1;
			$url = geturl ( arraytostring ( $urlarr ) ) . $a_name;
			$urlarr ['page'] = $curpage - 1;
			$url2 = geturl ( arraytostring ( $urlarr ) ) . $a_name;
		}
		
		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $url . '">|&lt;</a>' : '') . ($curpage > 1 ? '<a href="' . $url2 . '">&lt;</a>' : '');
		for($i = $from; $i <= $to; $i ++) {
			if ($phpurl) {
				$url = $mpurl . 'page=' . $i . $a_name;
			} else {
				$urlarr ['page'] = $i;
				if ($urlarr ['page'] == 1)
					unset ( $urlarr ['page'] );
				$url = geturl ( arraytostring ( $urlarr ) ) . $a_name;
			}
			$multipage .= $i == $curpage ? '<span class="xspace-current">' . $i . '</span>' : '<a href="' . $url . '">' . $i . '</a>';
		}
		
		if ($phpurl) {
			$url = $mpurl . 'page=' . ($curpage + 1) . $a_name;
			$url2 = $mpurl . 'page=' . $pages . $a_name;
		} else {
			$urlarr ['page'] = $curpage + 1;
			if ($urlarr ['page'] == 1)
				unset ( $urlarr ['page'] );
			$url = geturl ( arraytostring ( $urlarr ) ) . $a_name;
			$urlarr ['page'] = $pages;
			if ($urlarr ['page'] == 1)
				unset ( $urlarr ['page'] );
			$url2 = geturl ( arraytostring ( $urlarr ) ) . $a_name;
		}
		
		$multipage .= ($curpage < $pages ? '<a href="' . $url . '">&gt;</a>' : '') . ($to < $pages ? '<a href="' . $url2 . '" target="_self">&gt;|</a>' : '') . ($curpage == $maxpages ? '<a href="batch.message.php?action=maxpages&pages=' . $maxpages . '" >&gt;?</a>' : '') . ($pages > $page ? '' : '');
		$multipage = $multipage ? '<div class="xspace-page"><span class="xspace-totlerecord">' . $num . '</span><span class="xspace-totlepages">' . $curpage . '/' . $realpages . '</span>' . $multipage . '</div>' : '';
	}
	
	return $multipage;
}

/**
 * ���ɷ�ҳURL��ַ�����Լ����ݷ���ֵ��ajax����
 * 
 * @param String $sql����¼����
 * @param int $perpage�� ÿҳ��¼��
 * @param int $curpage�� ��ǰ��ҳ��
 * @param String $onclickMethod: ajax������
 * @return  ���ط�ҳHTML�����Լ���ѯ����ļ��ϣ��ֱ��Ӧ����'mulitpage' => $multipage, 'retult' => $result 
 */
function multiAndResultForAjax($sql, $perpage, $curpage, $onclickMethod, $phpurl = 1) {
	
	global $_SHTML;
	global $_SGLOBAL;
	$numSql = $sql;
//	echo $sql;
	$numSql = "select count(*) " . substr ( $numSql, strpos ( $numSql, 'from', 1 ), strlen ( $numSql ) - 1 );
	$querynum = $_SGLOBAL ['db']->query ( $numSql );
	$num = $_SGLOBAL ['db']->fetch_row ( $querynum );
	$num = $num [0];
	
	$curNum=($curpage - 1) * $perpage;
	if ($curNum > $num)
		messagebox ( 'error', 'start_listcount_error' );
		
	$sql = $sql . " LIMIT " . $curNum. "," . $perpage;
	$query = $_SGLOBAL ['db']->query ( $sql );
	$resultarray = array ();
	while ( $result = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		$resultarray [] = $result;
	}
	$maxpages = 999999999;
	$multipage = $a_name = '';
	if ($num > $perpage) {
		$page = 10;
		$offset = 2;
		$realpages = @ceil ( $num / $perpage );
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
		if ($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $curpage + $page - $offset - 1;
			if ($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if (($to - $from) < $page && ($to - $from) < $pages) {
					$to = $page;
				}
			} elseif ($to > $pages) {
				$from = $curpage - $pages + $to;
				$to = $pages;
				if (($to - $from) < $page && ($to - $from) < $pages) {
					$from = $pages - $page + 1;
				}
			}
		}
		
		if ($phpurl) {
			$url = 1;
			$url2 = ($curpage - 1);
		}
		
		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="#" onclick="' . $onclickMethod . '(' . $url . ')">|&lt;</a>' : '') . ($curpage > 1 ? '<a href="#" onclick="' . $onclickMethod . '(' . $url2 . ')">&lt;</a>' : '');
		for($i = $from; $i <= $to; $i ++) {
			if ($phpurl) {
				$url = $i;
			}
			$multipage .= $i == $curpage ? '<span class="xspace-current">' . $i . '</span>' : '<a  href="#" onclick="' . $onclickMethod . '(' . $url . ')">' . $i . '</a>';
		}
		
		if ($phpurl) {
			$url = $curpage + 1;
			$url2 = $pages;
		}
		
		$multipage .= ($curpage < $pages ? '<a href="#" onclick="' . $onclickMethod . '(' . $url . ')">&gt;</a>' : '') . ($to < $pages ? '<a href="#" onclick="' . $onclickMethod . '(' . $url2 . ')" target="_self">&gt;|</a>' : '') . ($curpage == $maxpages ? '<a  href="batch.message.php?action=maxpages&pages=' . $maxpages . '" >&gt;?</a>' : '') . ($pages > $page ? '' : '');
		$multipage = $multipage ? '<div class="xspace-page"><span class="xspace-totlerecord">' . $num . '</span><span class="xspace-totlepages">' . $curpage . '/' . $realpages . '</span>' . $multipage . '</div>' : '';
	}
	$results = array ('mulitpage' => $multipage, 'retult' => $resultarray );
	return $results;
}

//�����ļ�
function exportfile($array, $filename) {
	global $_SGLOBAL, $_SCONFIG;
	global $_SERVER;
	
	$array ['version'] = strip_tags ( S_VER );
	$time = sgmdate ( $_SGLOBAL ['timestamp'] );
	$exporttext = "# SupeSite Dump\r\n" . "# Version: SupeSite " . S_VER . "\r\n" . "# Time: $time\r\n" . "# From: $_SCONFIG[sitename] (" . S_URL . ")\r\n" . "#\r\n" . "# This file was BASE64 encoded\r\n" . "#\r\n" . "# SupeSite: http://www.supesite.com\r\n" . "# Please visit our website for latest news about SupeSite\r\n" . "# --------------------------------------------------------\r\n\r\n\r\n" . wordwrap ( base64_encode ( serialize ( $array ) ), 50, "\r\n", 1 );
	
	obclean ();
	header ( 'Content-Encoding: none' );
	header ( 'Content-Type: ' . (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' ) ? 'application/octetstream' : 'application/octet-stream') );
	header ( 'Content-Disposition: attachment; filename="' . $filename . '.txt"' );
	header ( 'Content-Length: ' . strlen ( $exporttext ) );
	header ( 'Pragma: no-cache' );
	header ( 'Expires: 0' );
	
	echo $exporttext;
	exit ();
}

//��SGMDATE������Ӧ
function sdate($dateformat, $timestamp) {
	echo sgmdate ( $timestamp, $dateformat );
}

//Ԥ������
function showpreviewimg($attach) {
	global $_SCONFIG;
	
	$img = '';
	if ($attach ['isimage']) {
		if (! empty ( $attach ['thumbpath'] )) {
			$filepath = $attach ['thumbpath'];
		} else {
			$filepath = $attach ['filepath'];
		}
		$img = '<img src="' . A_URL . '/' . $filepath . '" width="60" border="0">';
	} else {
		$img = '<img src="images/base/attachment.gif" width="32" height="32" border="0">';
	}
	return '<a href="batch.download.php?aid=' . $attach ['aid'] . '" target="_blank">' . $img . '</a>';
}

//�����ϴ��ĸ�������HTML�༭��
function getuploadinserthtml($uploadarr, $noinsert = 0, $theaid = 0) {
	global $_SCONFIG, $lang;
	
	$inserthtml = '';
	if (! empty ( $uploadarr ) && is_array ( $uploadarr )) {
		$inserthtml .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
		foreach ( $uploadarr as $listvalue ) {
			
			$listvalue ['uidcode'] = authcode ( $listvalue ['uid'] . '|' . $listvalue ['aid'], 'ENCODE' );
			
			$listvalue ['fileurl'] = A_URL . '/' . $listvalue ['filepath'];
			$listvalue ['thumburl'] = A_URL . '/' . $listvalue ['thumbpath'];
			$listvalue ['size'] = formatsize ( $listvalue ['size'] );
			$listvalue ['dateline'] = sgmdate ( $listvalue ['dateline'] );
			
			$inserthtml .= '<tr>';
			$inserthtml .= '<td style="width:60px">' . showpreviewimg ( $listvalue ) . '</td>';
			
			if (! empty ( $theaid ) && $theaid == $listvalue ['aid']) {
				$divsubject = '<input type="text" name="editsubject" id="editsubject" size="40" value="' . $listvalue ['subject'] . '" /><a href="javascript:;" onClick="attacheditsubmit(' . $listvalue ['aid'] . ')"><img src="admin/images/icon_succ.gif" style="width:22px;height:23px;border:0px" align="absmiddle" alt="OK" /></a>';
			} else {
				$divsubject = $listvalue ['subject'];
			}
			$inserthtml .= '<td><div id="div_upload_' . $listvalue ['aid'] . '" style="font-weight:bold">' . $divsubject . '</div>';
			$inserthtml .= '<a href="' . $listvalue ['fileurl'] . '" target="_blank">' . $listvalue ['filename'] . '</a> (' . $listvalue ['size'] . ')<br>';
			$inserthtml .= '<img src="admin/images/action_icon_edit.gif" style="width:16px;height:15px" align="absmiddle" border="0" /> <a href="batch.upload.php?action=edit&noinsert=' . $noinsert . '&aid=' . $listvalue ['aid'] . '&uc=' . rawurlencode ( $listvalue ['uidcode'] ) . '" target="phpframe">' . $lang ['edit'] . '</a>';
			$inserthtml .= ' | <a href="batch.upload.php?action=delete&noinsert=' . $noinsert . '&aid=' . $listvalue ['aid'] . '&uc=' . rawurlencode ( $listvalue ['uidcode'] ) . '" target="phpframe">' . $lang ['delete'] . '</a>';
			
			if (! $noinsert) {
				$attachurl = S_URL . '/batch.download.php?aid=' . $listvalue ['aid'];
				if ($listvalue ['isimage']) {
					$thehtml = '<a href="' . $attachurl . '" target="_blank"><img src="' . $listvalue ['fileurl'] . '" border="0"></a>';
				} else {
					$thehtml = '<a href="' . $attachurl . '" target="_blank"><img src="' . S_URL . '/images/base/attachment.gif" width="32" height="32" border="0"> ' . $listvalue ['filename'] . '(' . $listvalue ['size'] . ')</a>';
				}
				$inserthtml .= ' | <a href="javascript:;" onClick="insertHtml(\'' . shtmlspecialchars ( $thehtml ) . '\');return false;">' . $lang ['insert'] . '</a>';
			}
			$inserthtml .= '<input name="divupload[]" type="hidden" value="' . $listvalue ['aid'] . '" />';
			$inserthtml .= '</td></tr>';
		}
		
		$inserthtml .= '</table>';
	}
	return $inserthtml;
}

//�ӽ����ַ���


//����������
function getselectstr($var, $optionarray, $value = '', $other = '') {
	global $_SGET;
	
	$selectstr = '<select id="' . $var . '" name="' . $var . '"' . $other . '>';
	foreach ( $optionarray as $optionkey => $optionvalue ) {
		$selectstr .= '<option value="' . $optionkey . '">' . $optionvalue . '</option>';
	}
	if ($value == '' && isset ( $_SGET [$var] )) {
		$value = $_SGET [$var];
	}
	$selectstr = str_replace ( 'value="' . $value . '"', 'value="' . $value . '" selected', $selectstr );
	$selectstr .= '</select>';
	return $selectstr;
}

//��$sqlǰǿ�Ƽ��� SELECT
function getblocksql($sql) {
	$sql = trim ( $sql );
	$sql = str_replace ( ';', '', $sql );
	$sql = preg_replace ( "/^(select)/i", '', $sql );
	$sql = 'SELECT' . $sql;
	$sql = stripslashes ( $sql );
	return $sql;
}

//��Χ��ѯ����SQL����
function getscopequery($pre, $var, $paramarr, $isdate = 0) {
	global $_SGLOBAL;
	
	$wheresql = '';
	if (! empty ( $pre ))
		$pre = $pre . '.';
	if (! empty ( $paramarr [$var] )) {
		if ($isdate) {
			$paramarr [$var] = intval ( $paramarr [$var] );
			if ($paramarr [$var])
				$wheresql = $pre . $var . '>=' . ($_SGLOBAL ['timestamp'] - $paramarr [$var]);
		} else {
			$tarr = explode ( ',', $paramarr [$var] );
			if (count ( $tarr ) == 2) {
				$tarr [0] = intval ( trim ( $tarr [0] ) );
				$tarr [1] = intval ( trim ( $tarr [1] ) );
				if ($tarr [1] > $tarr [0]) {
					$wheresql = '(' . $pre . $var . '>=' . $tarr [0] . ' AND ' . $pre . $var . '<=' . $tarr [1] . ')';
				}
			}
		}
	}
	return $wheresql;
}

//��ȡָ��Ŀ¼�µ��ļ�
function sreaddir($dir, $ext = '') {
	
	$filearr = array ();
	if (is_dir ( $dir )) {
		$filedir = dir ( $dir );
		while ( false !== ($entry = $filedir->read ()) ) {
			if (! empty ( $ext )) {
				if (strtolower ( fileext ( $entry ) ) == strtolower ( $ext )) {
					$filearr [$entry] = $entry;
				}
			} else {
				if ($entry != '.' && $entry != '..') {
					$filearr [$entry] = $entry;
				}
			}
		}
		$filedir->close ();
	}
	return $filearr;
}

//TAG������
function tagshow($message, $tagarr) {
	global $_SGLOBAL;
	$message = preg_replace ( "/\s*(\<.+?\>)\s*/ies", "tagcode('\\1')", $message );
	foreach ( $tagarr as $ret ) {
		$message = preg_replace ( "/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(" . preg_quote ( $ret, '/' ) . ")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/sieU", "tagshowname('\\1', '\\2')", $message, 1 );
	}
	if (empty ( $_SGLOBAL ['tagcodecount'] ))
		$_SGLOBAL ['tagcodecount'] = 0;
	for($i = 1; $i <= $_SGLOBAL ['tagcodecount']; $i ++) {
		$message = str_replace ( "[\tSUPESITETAGCODE$i\t]", $_SGLOBAL ['tagcodehtml'] [$i], $message );
	}
	return $message;
}

//TAG����(����<xxx>)
function tagcode($str) {
	global $_SGLOBAL;
	if (empty ( $_SGLOBAL ['tagcodecount'] ))
		$_SGLOBAL ['tagcodecount'] = 0;
	$_SGLOBAL ['tagcodecount'] ++;
	$_SGLOBAL ['tagcodehtml'] [$_SGLOBAL ['tagcodecount']] = str_replace ( '\\"', '"', $str );
	return "[\tSUPESITETAGCODE$_SGLOBAL[tagcodecount]\t]";
}

//TAG������
function tagshowname($thename, $thetext) {
	$name = rawurlencode ( $thename );
	$thetext = str_replace ( '\\"', '"', $thetext );
	if (cutstr ( $thetext, 1 ) != '<') {
		return '<a href="javascript:;" onClick="javascript:tagshow(event, \'' . $name . '\');" target="_self"><u><strong>' . $thename . '</strong></u></a>' . $thetext;
	} else {
		return $thename . $thetext;
	}
}

//���Ѽ��
function checkfriend($perm, $uid) {
	global $_SGLOBAL;
	
	$isfriend = true;
	if ($perm && $uid != $_SGLOBAL ['supe_uid']) {
		$query = $_SGLOBAL ['db']->query ( 'SELECT COUNT(*) FROM ' . tname ( 'friends' ) . ' WHERE uid=\'' . $uid . '\' AND frienduid=\'' . $_SGLOBAL ['supe_uid'] . '\' AND grade IN (' . $perm . ')' );
		$count = $_SGLOBAL ['db']->result ( $query, 0 );
		if (empty ( $count )) {
			$isfriend = false;
		}
	}
	return $isfriend;
}

//�����ֱ��'I','II'�ַ�
function numtoI($num) {
	switch ($num) {
		case 1 :
			return 'I';
			break;
		case 2 :
			return 'II';
			break;
		case 3 :
			return 'III';
			break;
	}
}

//ȡ���˿ռ�ģ��
function getthemes() {
	$themesdir = S_ROOT . './themes';
	$theme_arr = array ();
	$dirarr = sreaddir ( $themesdir );
	if (! empty ( $dirarr )) {
		foreach ( $dirarr as $value ) {
			$themes = array ();
			$themephp = $themesdir . '/' . $value . '/theme.php';
			@include_once ($themephp);
			if (! empty ( $themes )) {
				$themes ['dir'] = $value;
				$theme_arr [] = $themes;
			}
		}
	}
	return $theme_arr;
}

//��ȡ��ʽģ��
function getthemefile($tplid, $tpltype) {
	$text = '';
	$dir = S_ROOT . './themes/' . $tplid . '/';
	if (is_readable ( $dir . $tpltype . '.html' )) {
		$text = getfile ( $dir . $tpltype . '.html' );
	}
	if (empty ( $text )) {
		$dir = S_ROOT . './themes/default/';
		$text = getfile ( $dir . $tpltype . '.html' );
	}
	
	return $text;
}

function encodeconvert($encode, $content, $to = 0) {
	global $chs, $_SCONFIG;
	
	if (! empty ( $encode ) && strtolower ( $encode ) != strtolower ( $_SCONFIG ['charset'] )) {
		include_once S_ROOT . './class/chinese.class.php';
		if ($to) {
			$chs = new Chinese ( $_SCONFIG ['charset'], $encode, $content );
		} else {
			$chs = new Chinese ( $encode, $_SCONFIG ['charset'], $content );
		}
		$content = $chs->ConvertIT ();
	}
	return $content;
}

function usetype($type) {
	global $_SCONFIG;
	if (empty ( $_SCONFIG ['channel'] [$type] )) {
		return false;
	} else {
		return true;
	}
}

//���$string���Ǳ������򷵻ؼ��ϡ������ַ���
function getdotstring($string, $vartype, $allownull = false, $varscope = array(), $sqlmode = 1, $unique = true) {
	
	if (is_array ( $string )) {
		$stringarr = $string;
	} else {
		if (substr ( $string, 0, 1 ) == '$') {
			return $string;
		}
		$string = str_replace ( '��', ',', $string );
		$string = str_replace ( ' ', ',', $string );
		$stringarr = explode ( ',', $string );
	}
	
	$newarr = array ();
	foreach ( $stringarr as $value ) {
		$value = trim ( $value );
		if ($vartype == 'int') {
			$value = intval ( $value );
		}
		if (! empty ( $varscope )) {
			if (in_array ( $value, $varscope )) {
				$newarr [] = $value;
			}
		} else {
			if ($allownull) {
				$newarr [] = $value;
			} else {
				if (! empty ( $value ))
					$newarr [] = $value;
			}
		}
	}
	
	if ($unique)
		$newarr = sarray_unique ( $newarr );
	
	if ($vartype == 'int') {
		$string = implode ( ',', $newarr );
	} else {
		if ($sqlmode) {
			$string = '\'' . implode ( '\',\'', $newarr ) . '\'';
		} else {
			$string = implode ( ',', $newarr );
		}
	}
	return $string;
}

//����������ͬ��ֵȥ��,ͬʱ������ļ���Ҳ���Ե�
function sarray_unique($array) {
	$newarray = array ();
	if (! empty ( $array ) && is_array ( $array )) {
		$array = array_unique ( $array );
		foreach ( $array as $value ) {
			$newarray [] = $value;
		}
	}
	return $newarray;
}

function mkmonth($month) {
	global $_SGLOBAL, $_SCONFIG, $lang;
	
	if (empty ( $_SGLOBAL ['member'] ['timeoffset'] ))
		$_SGLOBAL ['member'] ['timeoffset'] = $_SCONFIG ['timeoffset'];
	$marr = array ();
	$y = substr ( $month, 0, 4 );
	$m = substr ( $month, 4, 2 );
	$marr ['starttime'] = gmmktime ( 0, 0, 0, $m, 1, $y ) - $_SGLOBAL ['member'] ['timeoffset'] * 3600;
	if ($m == 12) {
		$newm = 1;
		$newy = $y + 1;
	} else {
		$newm = $m + 1;
		$newy = $y;
	}
	$marr ['endtime'] = gmmktime ( 0, 0, 0, $newm, 1, $newy ) - $_SGLOBAL ['member'] ['timeoffset'] * 3600;
	$marr ['month'] = $y . $lang ['year'] . $m . $lang ['month'];
	return $marr;
}

/**
 * ���ر�׼��ʱ��ʱ���
 * @param string $timestamp: ʱ���ʽΪ��YYYY-mm-dd H:i:s �� YYYY-mm-dd �� H:i:s
 * @return int ��׼ʱ���
 */
function sstrtotime($timestamp) {
	global $_SCONFIG;
	
	$timestamp = trim ( $timestamp ); //������β�ո�
	if (empty ( $timestamp ))
		return 0;
	$hour = $minute = $second = $month = $day = $year = 0;
	$exparr = $timearr = array ();
	if (strpos ( $timestamp, ' ' ) !== false && strpos ( $timestamp, '-' ) !== false) {
		$timearr = explode ( ' ', $timestamp );
		$exparr = explode ( '-', $timearr [0] );
		$year = empty ( $exparr [0] ) ? 0 : intval ( $exparr [0] );
		$month = empty ( $exparr [1] ) ? 0 : intval ( $exparr [1] );
		$day = empty ( $exparr [2] ) ? 0 : intval ( $exparr [2] );
		$exparr = explode ( ':', $timearr [1] );
		$hour = empty ( $exparr [0] ) ? 0 : intval ( $exparr [0] );
		$minute = empty ( $exparr [1] ) ? 0 : intval ( $exparr [1] );
		$second = empty ( $exparr [2] ) ? 0 : intval ( $exparr [2] );
	} elseif (strpos ( $timestamp, '-' ) !== false && strpos ( $timestamp, ' ' ) === false) {
		$exparr = explode ( '-', $timestamp );
		$year = empty ( $exparr [0] ) ? 0 : intval ( $exparr [0] );
		$month = empty ( $exparr [1] ) ? 0 : intval ( $exparr [1] );
		$day = empty ( $exparr [2] ) ? 0 : intval ( $exparr [2] );
	} elseif (! strpos ( $timestamp, '-' ) === false && strpos ( $timestamp, ' ' ) !== false) {
		$exparr = explode ( ':', $timestamp );
		$hour = empty ( $exparr [0] ) ? 0 : intval ( $exparr [0] );
		$minute = empty ( $exparr [1] ) ? 0 : intval ( $exparr [1] );
		$second = empty ( $exparr [2] ) ? 0 : intval ( $exparr [2] );
	} else {
		return 0;
	}
	return gmmktime ( $hour, $minute, $second, $month, $day, $year ) - $_SCONFIG ['timeoffset'] * 3600;
}

//ɾ������
function deleteitems($colname, $ids, $undel = 0) {
	global $_SGLOBAL, $_SCONFIG;
	
	if (is_array ( $ids ))
		$ids = simplode ( $ids );
	if ($undel) {
		//�ƶ�������վ
		$_SGLOBAL ['db']->query ( "UPDATE " . tname ( 'spaceitems' ) . " SET folder='3' WHERE $colname IN ($ids)" );
		return true;
	}
	
	$itemarr = array ();
	$itemidarr = array ();
	$uidarr = array ();
	$filearr = array ();
	
	//spaceitems//�ı��û�ͳ������
	$numarr = array ();
	$itemtypearr = array ();
	$itemuidarr = array ();
	$query = $_SGLOBAL ['db']->query ( "SELECT * FROM " . tname ( 'spaceitems' ) . " WHERE $colname IN ($ids)" );
	while ( $value = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		$type = $value ['type'];
		if (empty ( $itemarr [$type] ))
			$itemarr [$type] = array ();
		if (empty ( $numarr [$value ['uid']] [$type] ))
			$numarr [$value ['uid']] [$type] = 0;
		if (empty ( $numarr [$value ['uid']] ['all'] ))
			$numarr [$value ['uid']] ['all'] = 0;
		$itemarr [$type] [] = $value ['itemid'];
		$uidarr [$value ['uid']] = $value ['uid'];
		$itemidarr [] = $value ['itemid'];
		if ($type != 'news') {
			$numarr [$value ['uid']] ['all'] ++;
			$numarr [$value ['uid']] [$type] ++;
		}
		$itemtypearr [$value ['itemid']] = $value ['type'];
		$itemuidarr [$value ['itemid']] = $value ['uid'];
	}
	if (empty ( $itemidarr ))
		return false;
	$itemids = implode ( '\',\'', $itemidarr );
	
	//������
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'spaceitems' ) . " WHERE itemid IN ('$itemids')" );
	
	//��̳����
	dbconnect ( 1 );
	$_SGLOBAL ['db_bbs']->query ( 'UPDATE ' . tname ( 'threads', 1 ) . ' SET itemid=\'0\' WHERE itemid IN (\'' . $itemids . '\')', 'SILENT' );
	
	//����
	foreach ( $_SGLOBAL ['type'] as $type ) {
		$tablename = tname ( gettypetablename ( $type ) );
		if (in_array ( $type, array ('image', 'goods', 'video' ) ) && ! empty ( $itemarr [$type] )) {
			$query = $_SGLOBAL ['db']->query ( "SELECT * FROM $tablename WHERE itemid IN (" . simplode ( $itemarr [$type] ) . ")" );
			while ( $value = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
				if (! empty ( $value ['videoname'] )) {
					//Ӱ��
					if (! empty ( $value ['image'] )) {
						$filearr [] = S_ROOT . './' . $value ['image'];
					}
					if (! empty ( $value ['file'] )) {
						$filearr [] = S_ROOT . './' . $value ['file'];
					}
					
					//Ӱ��δת������Ƶ
					if (empty ( $value ['file'] ) || empty ( $value ['image'] )) {
						$videotypearr = explode ( ',', $_SCONFIG ['videotypes'] );
						foreach ( $videotypearr as $vtype ) {
							$filearr [] = S_ROOT . './video/temp/' . $value ['videoname'] . '.' . $vtype;
						}
					}
				} else {
					//����
					if (! empty ( $value ['image'] ))
						$filearr [] = A_DIR . '/' . $value ['image'];
					if (! empty ( $value ['thumb'] ))
						$filearr [] = A_DIR . '/' . $value ['thumb'];
				}
			}
		}
		$_SGLOBAL ['db']->query ( "DELETE FROM $tablename WHERE itemid IN ('$itemids')" );
	}
	
	//attachments//���ı��û�ͳ������
	$uidattachs = array ();
	$query = $_SGLOBAL ['db']->query ( "SELECT * FROM " . tname ( 'attachments' ) . " WHERE itemid IN ('$itemids')" );
	while ( $value = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		//Ӱ������?
		if ($value ['type'] == 'video') {
			if (! empty ( $value ['filepath'] ))
				$filearr [] = S_ROOT . './' . $value ['filepath'];
			if (! empty ( $value ['thumbpath'] ))
				$filearr [] = S_ROOT . './' . $value ['thumbpath'];
		} else {
			if (! empty ( $value ['filepath'] ))
				$filearr [] = A_DIR . '/' . $value ['filepath'];
			if (! empty ( $value ['thumbpath'] ))
				$filearr [] = A_DIR . '/' . $value ['thumbpath'];
		}
		
		if (empty ( $uidattachs [$value ['uid']] ))
			$uidattachs [$value ['uid']] = 0;
		$uidattachs [$value ['uid']] = $uidattachs [$value ['uid']] + $value ['size'];
	}
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'attachments' ) . " WHERE itemid IN ('$itemids')" );
	
	//�����û��ռ�ʹ��userspacefields attachsize
	//foreach ($uidattachs as $key => $value) {
	//$_SGLOBAL['db']->query("UPDATE ".tname('userspacefields')." SET attachsize=attachsize-$value WHERE uid='$key'");
	//}
	

	//favorites
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'favorites' ) . " WHERE itemid IN ('$itemids')" );
	
	//��Ʒgoodsprice
	if (! empty ( $itemarr ['goods'] ))
		$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'goodsprice' ) . " WHERE itemid IN ('$itemids')" );
	
		//spacecomments
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'spacecomments' ) . " WHERE itemid IN ('$itemids')" );
	
	//�û�����spacecache
	foreach ( $uidarr as $value ) {
		clearspacecache ( $value );
	}
	
	//spacetags//����tags��ͳ����Ϣ
	$tagidarr = array ();
	$query = $_SGLOBAL ['db']->query ( "SELECT * FROM " . tname ( 'spacetags' ) . " WHERE itemid IN ('$itemids')" );
	while ( $value = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		if (empty ( $tagidarr [$value ['tagid']] [$itemtypearr [$value ['itemid']]] ))
			$tagidarr [$value ['tagid']] [$itemtypearr [$value ['itemid']]] = 0;
		if (empty ( $tagidarr [$value ['tagid']] ['all'] ))
			$tagidarr [$value ['tagid']] ['all'] = 0;
		$tagidarr [$value ['tagid']] ['all'] ++;
		$tagidarr [$value ['tagid']] [$itemtypearr [$value ['itemid']]] ++;
	}
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'spacetags' ) . " WHERE itemid IN ('$itemids')" );
	
	//tracks
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'tracks' ) . " WHERE itemid IN ('$itemids')" );
	
	//�ٱ���Ϣ
	$_SGLOBAL ['db']->query ( "DELETE FROM " . tname ( 'reports' ) . " WHERE itemid IN ('$itemids')" );
	
	//�����û�ͳ������(attachsize��������)
	foreach ( $uidarr as $uid ) {
		$num = $numarr [$uid];
		$colarr = array ();
		$colarr [] = 'spaceallnum=spaceallnum-' . $num ['all'];
		foreach ( $_SGLOBAL ['type'] as $value ) {
			if ($value != 'news' && ! empty ( $num [$value] ))
				$colarr [] = 'space' . $value . 'num=space' . $value . 'num-' . $num [$value];
		}
		$_SGLOBAL ['db']->query ( "UPDATE " . tname ( 'userspaces' ) . " SET " . implode ( ',', $colarr ) . " WHERE uid='$uid'" );
	}
	
	//����tagͳ������
	foreach ( $tagidarr as $tagid => $num ) {
		$colarr = array ();
		$colarr [] = 'spaceallnum=spaceallnum-' . $num ['all'];
		foreach ( $_SGLOBAL ['type'] as $value ) {
			if (! empty ( $num [$value] ))
				$colarr [] = 'space' . $value . 'num=space' . $value . 'num-' . $num [$value];
		}
		$_SGLOBAL ['db']->query ( "UPDATE " . tname ( 'tags' ) . " SET " . implode ( ',', $colarr ) . " WHERE tagid='$tagid'" );
	}
	
	//ɾ������
	if (! empty ( $filearr )) {
		foreach ( $filearr as $value ) {
			if (! @unlink ( $value ))
				errorlog ( 'attachment', 'Unlink ' . $value . ' Error.' );
		}
	}
	
	//ɾ��html�ļ�
	foreach ( $itemidarr as $itemid ) {
		if ($itemtypearr [$itemid] == 'news') {
			$id = $itemid;
		} else {
			$id = $itemuidarr [$itemid];
		}
		$idvalue = ($id > 9) ? substr ( $id, - 2, 2 ) : $id;
		$filedir = H_DIR . '/' . $idvalue;
		if (is_dir ( $filedir )) {
			$filearr = sreaddir ( $filedir );
			foreach ( $filearr as $file ) {
				if (preg_match ( "/\-$itemid(\.|\-)/i", $file )) {
					@unlink ( $filedir . '/' . $file );
				}
			}
		}
	}
}

//��ȡϵͳ����
function getcategory($type, $space = '|----') {
	global $_SGLOBAL;
	
	include_once (S_ROOT . './class/tree.class.php');
	$tree = new Tree ( $type );
	$query = $_SGLOBAL ['db']->query ( 'SELECT * FROM ' . tname ( 'categories' ) . ' WHERE type=\'' . $type . '\' ORDER BY upid, displayorder' );
	$miniupid = '';
	while ( $value = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
		if ($miniupid == '')
			$miniupid = $value ['upid'];
		$tree->setNode ( $value ['catid'], $value ['upid'], $value );
	}
	//��Ŀ¼
	$listarr = array ();
	$categoryarr = $tree->getChilds ( $miniupid );
	foreach ( $categoryarr as $key => $catid ) {
		$cat = $tree->getValue ( $catid );
		$cat ['pre'] = $tree->getLayer ( $catid, $space );
		$listarr [$cat ['catid']] = $cat;
	}
	return $listarr;

}

function sendpm($action, $paramarr) {
	
	global $_SGLOBAL, $lang;
	$setsqlarr = array ();
	if (empty ( $paramarr ) || ! is_array ( $paramarr ))
		return false;
	if (empty ( $paramarr ['touid'] ))
		return false;
	$setsqlarr = array ('msgfrom' => $_SGLOBAL ['supe_username'], 'msgfromid' => intval ( $_SGLOBAL ['supe_uid'] ), 'msgtoid' => intval ( $paramarr ['touid'] ), 'folder' => 'inbox', 'new' => '1', 'subject' => '', 'message' => '', 'dateline' => $_SGLOBAL ['timestamp'] );
	switch ($action) {
		case 'addfriend' :
			$setsqlarr ['subject'] = saddslashes ( $_SGLOBAL ['supe_username_show'] . $lang ['pma_add_friend_subject'] );
			$setsqlarr ['message'] = saddslashes ( $lang ['pma_salutation'] . $_SGLOBAL ['supe_username_show'] . $lang ['pma_add_friend_content_1'] . sgmdate ( $_SGLOBAL ['timestamp'] ) . $lang ['pma_add_friend_content'] );
			break;
		case 'invite' :
			$setsqlarr ['subject'] = saddslashes ( $_SGLOBAL ['supe_username_show'] . $lang ['pma_invite_friend_subject'] . $paramarr ['groupname'] . $lang ['pma_invite_friend_subject_1'] );
			$setsqlarr ['message'] = saddslashes ( $lang ['pma_salutation'] . $lang ['pma_invite_friend_message_0'] . $_SGLOBAL ['supe_username_show'] . $lang ['pma_invite_friend_message_1'] . $paramarr ['sitename'] . $lang ['pma_invite_friend_message_2'] . $paramarr ['groupname'] . $lang ['pma_invite_friend_message_3'] . '[url]' . $paramarr ['siteurl'] . '/?action/mygroup/gid/' . $paramarr ['gid'] . '[/url]' . $lang ['pma_invite_friend_message_4'] . '[url]' . $paramarr ['siteurl'] . '/spacecp.php?action=groups&gid=' . $paramarr ['gid'] . '&op=accept[/url] ' . $lang ['pma_invite_friend_message_5'] . '[url]' . $paramarr ['siteurl'] . '/spacecp.php?action=groups&gid=' . $paramarr ['gid'] . '&op=refuse[/url] ' . $lang ['pma_invite_friend_message_6'] );
			break;
		default :
			return false;
			break;
	}
	
	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ( $setsqlarr as $insert_key => $insert_value ) {
		$insertkeysql .= $comma . $insert_key;
		$insertvaluesql .= $comma . '\'' . $insert_value . '\'';
		$comma = ', ';
	}
	
	dbconnect ( 1 );
	//�û�����Ϣ
	$_SGLOBAL ['db_bbs']->query ( 'INSERT INTO ' . tname ( 'pms', 1 ) . ' (' . $insertkeysql . ') VALUES (' . $insertvaluesql . ') ' );
	$_SGLOBAL ['db_bbs']->query ( "UPDATE " . tname ( 'members', 1 ) . " SET newpm='1' WHERE uid='$setsqlarr[msgtoid]'" );
	$_SGLOBAL ['db']->query ( "UPDATE " . tname ( 'members' ) . " SET newpm='1' WHERE uid='$setsqlarr[msgtoid]'" );
	
	return true;
}

//������Ƶ��
function mkvideoname($videoname) {
	$filearr = array ('file' => '', 'image' => '' );
	
	$vdir = 'video/' . substr ( $videoname, - 14, 6 );
	if (! is_dir ( S_ROOT . './' . $vdir ))
		@mkdir ( S_ROOT . './' . $vdir, 0777 ); //����Ŀ¼
	$vdir .= '/' . $videoname;
	
	$filearr ['file'] = $vdir . '.flv';
	$filearr ['image'] = $vdir . '.jpg';
	
	return $filearr;
}

//�ظ��б�
function getcommenthtml($commentlist) {
	
	global $_SGLOBAL, $lang;
	
	$html = '';
	foreach ( $commentlist as $value ) {
		
		if (! empty ( $value ['message'] ))
			$value ['message'] = bbcode ( $value ['message'] );
		
		if (empty ( $value ['authorid'] )) {
			if ($value ['url']) {
				$value ['photo'] = S_URL . '/images/base/pic_trackback.gif';
				$value ['message'] = "<p><a href=\"$value[url]\" target=\"_blank\">$value[subject]</a></p>" . $value ['message'];
			}
			$value ['photo'] = "<img src=\"$value[photo]\" class=\"xspace-signavatar xspace-imgstyle\" />";
		} else {
			$url = geturl ( "uid/$value[authorid]" );
			$value ['photo'] = "<a href=\"" . $url . "\" target=\"_blank\"><img src=\"$value[photo]\" class=\"xspace-signavatar xspace-imgstyle\" alt=\"" . $value ['spacename'] . "\" /></a>";
			$value ['author'] = "<a href=\"" . $url . "\" target=\"_blank\">$value[author]</a>";
		}
		$value ['dateline'] = sgmdate ( $value ['dateline'] );
		
		if (! empty ( $value ['rates'] )) {
			$value ['message'] = "$lang[rate_pre] <span style=\"font-size:18px;font-weight:bold;\">$value[rates]</span> $lang[fen]<br />" . $value ['message'];
		}
		
		$html .= "
		<dl id=\"xspace-comment{$value['cid']}\">
		<dt>
		$value[photo]
		
		<a href=\"javascript:;\" onclick=\"getQuote($value[cid])\" class=\"xspace-quote\">$lang[quote]</a>
		<a href=\"javascript:;\" onclick=\"javascript:deletecomment($value[cid]);\" class=\"xspace-del\">$lang[delete]</a>

		$value[author] <span class=\"xspace-smalltxt\"> &nbsp; / &nbsp; $value[dateline]</span>
		</dt>
		<dd>
		$value[message]
		</dd>
		</dl>
		";
	}
	return $html;
}

//�滻CSS�е�ͼƬ��ַ
function cssimage_replace($css, $themedir) {
	return str_replace ( '{THEME_URL}', S_URL . '/' . $themedir, $css );
}

function sjammer() {
	global $_SGLOBAL, $_SCONFIG;
	
	$randomstr = '';
	for($i = 0; $i < mt_rand ( 5, 15 ); $i ++) {
		$randomstr .= chr ( mt_rand ( 0, 59 ) ) . chr ( mt_rand ( 63, 126 ) );
	}
	return mt_rand ( 0, 1 ) ? '<span style="display:none">' . $_SCONFIG ['sitename'] . $randomstr . '</span>' . "<br />" : "<br />" . '<span style="display:none">' . $randomstr . $_SGLOBAL ['supe_uid'] . '</span>';
}

//��ȡ��̳�����ļ���url��ַ
function getbbsattachment($attach) {
	global $_SCONFIG;
	
	if (strpos ( $attach ['attachment'], '://' ) === false) {
		$attachurl = empty ( $_SCONFIG ['bbs_ftp'] ['attachurl'] ) ? B_A_URL : (empty ( $attach ['remote'] ) ? B_A_URL : $_SCONFIG ['bbs_ftp'] ['attachurl']);
		if (empty ( $item ['thumb'] )) {
			return $attachurl . '/' . $attach ['attachment'];
		} else {
			return $attachurl . '/' . $attach ['attachment'] . '.thumb.jpg';
		}
	} else {
		return $attach ['attachment'];
	}
}

//�и�url
function cuturl($url, $length = 65) {
	$urllink = "<a href=\"" . (substr ( strtolower ( $url ), 0, 4 ) == 'www.' ? "http://$url" : $url) . '" target="_blank">';
	if (strlen ( $url ) > $length) {
		$url = substr ( $url, 0, intval ( $length * 0.5 ) ) . ' ... ' . substr ( $url, - intval ( $length * 0.3 ) );
	}
	$urllink .= $url . '</a>';
	return $urllink;
}

//����


function itembatch($item) {
	global $lang, $space;
	
	$type = $item ['type'];
	$messagelen = 200;
	
	$item ['typeurl'] = '<a href="' . geturl ( "uid/$item[uid]/action/spacelist/type/$item[type]" ) . '">' . $lang [$item ['type']] . '</a>';
	
	//ժҪ
	if (! empty ( $item ['message'] )) {
		if ($type == 'blog') {
			if (! empty ( $space ['summarylen'] )) {
				$item ['message'] = jsstrip ( cuthtml ( $item ['message'], $space ['summarylen'], 1 ) );
			}
		} else {
			$item ['message'] = trim ( cutstr ( strip_tags ( $item ['message'] ), $messagelen, 1 ) );
		}
	}
	//����
	if (empty ( $item ['url'] )) {
		$item ['url'] = geturl ( 'uid/' . $item ['uid'] . '/action/viewspace/itemid/' . $item ['itemid'] );
	} else {
		$item ['cuturl'] = cuturl ( $item ['url'] );
		$item ['itemurl'] = geturl ( 'uid/' . $item ['uid'] . '/action/viewspace/itemid/' . $item ['itemid'] );
	}
	
	//�ö�
	if (! empty ( $item ['top'] )) {
		$item ['top'] = '<img src="' . S_URL . '/images/base/top_' . $item ['top'] . '.gif" alt="Top ' . $item ['top'] . '" style="vertical-align: middle;" /> ';
	} else {
		$item ['top'] = '';
	}
	//����
	if (! empty ( $item ['digest'] )) {
		$item ['digest'] = '<img src="' . S_URL . '/images/base/digest_' . $item ['digest'] . '.gif" alt="Digest ' . $item ['digest'] . '" style="vertical-align: middle;" /> ';
	} else {
		$item ['digest'] = '';
	}
	$item ['typename'] = $lang [$type];
	
	//���tag
	if (! empty ( $item ['relativetags'] )) {
		$item ['relativetags'] = $item ['tags'] = unserialize ( $item ['relativetags'] );
	} else {
		$item ['relativetags'] = $item ['tags'] = array ();
	}
	
	//����ͼ
	if (! empty ( $item ['thumb'] )) {
		$item ['thumb'] = A_URL . '/' . $item ['thumb'];
	} else {
		$item ['thumb'] = S_URL . '/images/base/nopic.gif';
	}
	//ͼƬ
	if (! empty ( $item ['image'] )) {
		if ($type == 'video') {
			if (empty ( $item ['subtype'] )) {
				$item ['image'] = S_URL . '/' . $item ['image'];
			} else {
				$item ['image'] = A_URL . '/' . $item ['image'];
			}
		} else {
			$item ['image'] = A_URL . '/' . $item ['image'];
		}
	} else {
		if ($type == 'video') {
			$item ['image'] = S_URL . '/images/base/video_nothumb.jpg';
		} else {
			$item ['image'] = S_URL . '/images/base/nopic.gif';
		}
	}
	
	return $item;
}

//���¸��˿ռ仺�溯��/�ֱ�
function updatespacecache($uid, $blockname, $blocktime) {
	global $_SGLOBAL, $_SBLOCK, $type;
	
	$tablename = getspacecachetablename ( $uid );
	$thetable = tname ( 'spacecache' );
	
	if ($_SGLOBAL ['creattable'] && $tablename != $thetable) {
		$query = $_SGLOBAL ['db']->query ( "SHOW CREATE TABLE $thetable" );
		$creattable = $_SGLOBAL ['db']->fetch_array ( $query );
		$sql = str_replace ( $thetable, $tablename, $creattable ['Create Table'] );
		$_SGLOBAL ['db']->query ( $sql, 'SILENT' ); //�����ֱ�
	}
	
	$blocktime = $_SGLOBAL ['timestamp'] + $blocktime;
	$blockvalue = addslashes ( serialize ( $_SBLOCK [$blockname] ) );
	
	//������Ŀ
	if ($blockname == 'itemtype') {
		$blockname = 'itemtype_' . $type;
	}
	$cacheids = array_keys ( $_SGLOBAL ['spaceblocks'], $blockname );
	if (! empty ( $cacheids [0] ))
		$_SGLOBAL ['db']->query ( "REPLACE INTO $tablename (uid, cacheid, value, updatetime) VALUES ('$uid', '$cacheids[0]', '$blockvalue', '$blocktime')" );

}

//��ȡ���˿ռ仺��
function getspacecache($uid) {
	global $_SGLOBAL, $_SBLOCK, $type;
	$tablename = getspacecachetablename ( $uid );
	
	$_SGLOBAL ['creattable'] = true;
	if ($query = $_SGLOBAL ['db']->query ( "SELECT * FROM $tablename WHERE uid='$uid'", 'SILENT' )) {
		$_SGLOBAL ['creattable'] = false;
		while ( $value = $_SGLOBAL ['db']->fetch_array ( $query ) ) {
			if ($value ['updatetime'] > $_SGLOBAL ['timestamp']) {
				$blockkey = $_SGLOBAL ['spaceblocks'] [$value ['cacheid']];
				if (strexists ( $blockkey, 'itemtype' )) {
					if ($blockkey == 'itemtype_' . $type) {
						$_SBLOCK ['itemtype'] = unserialize ( $value ['value'] );
					}
				} else {
					$_SBLOCK [$blockkey] = unserialize ( $value ['value'] );
				}
			}
		}
	}
}

//��ո��˿ռ仺��
function clearspacecache($uid, $blocknames = '') {
	global $_SGLOBAL, $_SBLOCK;
	$tablename = getspacecachetablename ( $uid );
	
	$blockids = array ();
	if (! empty ( $blocknames )) {
		if (is_array ( $blocknames )) {
			foreach ( $blocknames as $value ) {
				$ids = array_keys ( $_SGLOBAL ['spaceblocks'], $value );
				$blockids [] = $ids [0];
			}
		} else {
			$ids = array_keys ( $_SGLOBAL ['spaceblocks'], $blocknames );
			$blockids [] = $ids [0];
		}
	}
	if (! empty ( $blockids )) {
		$_SGLOBAL ['db']->query ( "DELETE FROM $tablename WHERE uid='$uid' AND cacheid IN (" . simplode ( $blockids ) . ")", 'SILENT' );
	} else {
		$_SGLOBAL ['db']->query ( "DELETE FROM $tablename WHERE uid='$uid'", 'SILENT' );
	}
}

//��ȡ���˻������
function getspacecachetablename($uid) {
	global $perspacenum;
	
	$tablename = tname ( 'spacecache' );
	if (! empty ( $perspacenum ) && $perspacenum >= 100) {
		//ȷ������
		$thetableid = intval ( $uid / $perspacenum ) + 1;
		if ($thetableid > 1) {
			$tablename = tname ( 'spacecache' . $thetableid );
		}
	}
	return $tablename;
}

//֧����
function trade_payurl($item) {
	global $_SGLOBAL, $charset;
	
	$args = array ('service' => 'trade_create_by_buyer', //note �ӿ�����
'partner' => DISCUZ_PARTNER, //note �������
'notify_url' => '', //note ֪ͨURL
'show_url' => geturl ( "uid/$item[uid]/action/viewspace/itemid/$item[itemid]", 1 ), //note ��ƷURL
'_input_charset' => $charset, //note �ַ���
'subject' => $item ['subject'], //note ��Ʒ����
'body' => $item ['subject'], //note ��Ʒ����
'out_trade_no' => $_SGLOBAL ['supe_uid'] . $_SGLOBAL ['timestamp'] . rand ( 0, 99 ), //note ���׵���
'price' => $item ['price'], //note ��Ʒ�۸�
'quantity' => $item ['buynum'], //note ��������
'logistics_type' => $item ['chargetype'], //note ��������
'logistics_fee' => $item ['chargeprice'], //note ��������
'logistics_payment' => $item ['chargepayment'], //note ����֧����ʽ
'payment_type' => 1, 'seller_email' => $item ['alipay'] ); //note ����֧����email
	

	ksort ( $args );
	$urlstr = $sign = '';
	foreach ( $args as $key => $val ) {
		if ($val != '')
			$sign .= '&' . $key . '=' . $val;
		$urlstr .= $key . '=' . rawurlencode ( $val ) . '&';
	}
	$sign = substr ( $sign, 1 );
	$sign = md5 ( $sign . DISCUZ_SECURITYCODE );
	return 'http://www.alipay.com/redir.do?id=307&site=allbbs&target=' . rawurlencode ( 'https://www.alipay.com/cooperate/gateway.do?' . $urlstr . 'sign=' . $sign . '&sign_type=MD5' );
}

?>