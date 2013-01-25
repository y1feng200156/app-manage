<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	XML RPC

	$RCSfile: xmlrpc.class.php,v $
	$Revision: 1.15 $
	$Date: 2007/05/10 15:06:34 $
*/

if (!defined('IN_SUPESITE')) {
	exit ('Access Denied');
}

class xmlrpc {

	var $xmlserver;
	var $callback;
	var $xmlmessage;
	var $db;
	var $timestamp;

	function __construct() {
		global $_SGLOBAL;
		$this->callback = $this->xmlrpcApi();
		$this->xmlmessage = new stdClass();
		$this->db = $_SGLOBAL['db'];
		$this->timestamp = $_SGLOBAL['timestamp'];
	}
	
	function xmlrpc() {
		$this->__construct();
	}

	function xmlrpcSet() {
		return new xmlrpc();
	}
	
	function xmlrpcServer() {
		
		$data = empty($GLOBALS['HTTP_RAW_POST_DATA'])?'':$GLOBALS['HTTP_RAW_POST_DATA'];
		if (!$data) {
			$this->sendFault(1, 'Invalid Method Call');
		} else {
			$data = addslashes($data);
		}
		$this->xmlmessage->structTypes = array ();
		$this->xmlmessage->structs = array ();
		$this->xmlmessage->struct_name = array ();
		if ($this->xmlrpcParse($data)) {
			$data = $this->xmlmessage->messages;
			$result = $this->xmlrpcCall($this->xmlmessage->methodname, $this->xmlmessage->params);
			$rxml = $this->xmlrpcValue($result);
			$outxml = $this->xmlrpcValueXML($rxml);
			$outxml = "<methodResponse><params><param><value>$outxml</value></param></params></methodResponse>";
			$this->xmlrpcOutXML($outxml);
		}
	}

	function xmlrpcApi() {
		$api = array (
			'blogger.getUsersBlogs' => 'getUserBlog',
			'blogger.getUserInfo' => 'getUserInfo',
			'metaWeblog.newPost' => 'newPost',
			'metaWeblog.editPost' => 'editPost',
			'metaWeblog.getPost' => 'getPost',
			'metaWeblog.newMediaObject' => 'newMediaObject',
			'metaWeblog.getCategories' => 'getCategories',
			'metaWeblog.getRecentPosts' => 'getRecentPosts'
		);
		return $api;
	}

	function xmlrpcParse($data) {
		$this->xmlmessage->messages = preg_replace('/<\?xml(.*)?\?'.'>/', '', $data);
		if (trim($this->xmlmessage->messages) == '') {
			return false;
		}

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($parser, $this->xmlmessage);
		xml_set_element_handler($parser, array (& $this, 'xmltag_open'), array (& $this, 'xmltag_close'));
		xml_set_character_data_handler($parser, array (& $this,	'xml_data'));
		$message = $this->xmlmessage->messages;
		if (!xml_parse($parser, $message)) {
			return false;
		}
		xml_parser_free($parser);
		if ($this->xmlmessage->messageType == 'fault') {
			return false;
		}
		return true;
	}

	function xmltag_open($parser, $tag, $attr) {
		$this->xmlmessage->tag_content = '';
		$this->xmlmessage->last_open = $tag;
		switch ($tag) {
			case 'methodCall' :
			case 'methodResponse' :
			case 'fault' :
				$this->xmlmessage->messageType = $tag;
				break;
			case 'data' :
				$this->xmlmessage->structTypes[] = 'array';
				$this->xmlmessage->structs[] = array ();
				break;
			case 'struct' :
				$this->xmlmessage->structTypes[] = 'struct';
				$this->xmlmessage->structs[] = array ();
				break;
		}
	}

	function xml_data($parser, $data) {
		$this->xmlmessage->tag_content .= $data;
	}

	function xmltag_close($parser, $tag) {
		$flag = false;
		switch ($tag) {
			case 'int' :
			case 'i4' :
				$value = intval(trim($this->xmlmessage->tag_content));
				$flag = true;
				break;
			case 'double' :
				$value = (double) trim($this->xmlmessage->tag_content);
				$flag = true;
				break;
			case 'string' :
				$value = $this->xmlmessage->tag_content;
				$flag = true;
				break;
			case 'dateTime.iso8601' :
				$value = $this->convertDate($this->xmlmessage->tag_content);
				$flag = true;
				break;
			case 'value' :
				if (trim($this->xmlmessage->tag_content) != '' || $this->xmlmessage->last_open == 'value') {
					$value = (string) trim($this->xmlmessage->tag_content);
					$flag = true;
				}
				break;
			case 'boolean' :
				$value = (boolean) trim($this->xmlmessage->tag_content);
				$flag = true;
				break;
			case 'base64' :
				$value = base64_decode(trim($this->xmlmessage->tag_content));
				$flag = true;
				break;
			case 'data' :
			case 'struct' :
				$value = array_pop($this->xmlmessage->structs);
				array_pop($this->xmlmessage->structTypes);
				$flag = true;
				break;
			case 'member' :
				array_pop($this->xmlmessage->struct_name);
				break;
			case 'name' :
				$this->xmlmessage->struct_name[] = trim($this->xmlmessage->tag_content);
				break;
			case 'methodName' :
				$this->xmlmessage->methodname = trim($this->xmlmessage->tag_content);
				break;
		}
		if ($flag) {
			if (count($this->xmlmessage->structs) > 0) {
				if ($this->xmlmessage->structTypes[count($this->xmlmessage->structTypes) - 1] == 'struct') {
					$this->xmlmessage->structs[count($this->xmlmessage->structs) - 1][$this->xmlmessage->struct_name[count($this->xmlmessage->struct_name) - 1]] = $value;
				} else {
					$this->xmlmessage->structs[count($this->xmlmessage->structs) - 1][] = $value;
				}
			} else {
				$this->xmlmessage->params[] = $value;
			}
		}
		
		
		if (!in_array($tag, array ('data', 'struct', 'member'))) {
			$this->xmlmessage->tag_content = '';
		}
	}

	function xmlrpcValue($data, $type = false) {
		$value = new stdClass();
		$value->data = $data;
		if (!$type) {
			$type = $this->xmlrpcType($value);
		}
		$value->type = $type;
		if ($type == 'struct') {
			foreach ($value->data as $key => $v) {
				$value->data[$key] = $this->xmlrpcValue($v);
			}
		}
		if ($type == 'array') {
			for ($i = 0, $j = count($value->data); $i < $j; $i++) {
				$value->data[$i] = $this->xmlrpcValue($value->data[$i]);
			}
		}
		return $value;
	}

	function xmlrpcValueXML($data) {
		switch ($data->type) {
			case 'boolean' :
				return '<boolean>'.($data->data) ? '1' : '0'.'</boolean>';
				break;
			case 'int' :
				return '<int>'.$data->data.'</int>';
				break;
			case 'double' :
				return '<double>'.$data->data.'</double>';
				break;
			case 'string' :
				return '<string><![CDATA['.$data->data.']]></string>';
				break;
			case 'array' :
				$return = '<array><data>';
				foreach ($data->data as $item) {
					$return .= '<value>'.$this->xmlrpcValueXML($item).'</value>';
				}
				$return .= '</data></array>';
				return $return;
				break;
			case 'struct' :
				$return = '<struct>'."\n";
				foreach ($data->data as $name => $item) {
					$return .= '<member><name>'.$name.'</name>';
					$return .= '<value>'.$this->xmlrpcValueXML($item).'</value></member>';
				}
				$return .= '</struct>';
				return $return;
				break;
			case 'date' :
				return '<dateTime.iso8601>'.($data->data->date).'</dateTime.iso8601>';
				break;
			case 'base64' :
				return '<base64>'.base64_encode($data->data).'</base64>';
				break;
			default :
				break;
		}
	}

	function xmlrpcType(& $value) {
		if (is_bool($value->data)) {
			return 'boolean';
		}
		if (is_double($value->data)) {
			return 'double';
		}
		if (is_int($value->data)) {
			return 'int';
		}
		if (is_array($value->data)) {
			return empty ($value->data) || range(0, count($value->data) - 1) === array_keys($value->data) ? 'array' : 'struct';
		}
		if (is_object($value->data)) {
			if ($value->data->is_date) {
				return 'date';
			}
			if ($value->data->is_base64) {
				return 'base64';
			}
			$value->data = get_object_vars($value->data);
			return 'struct';
		}
		return 'string';
	}

	function xmlrpcCall($methodname, $args) {
		$func = $this->callback[$methodname];
		return call_user_func_array(array (
			& $this,
			$func
		), $args);
	}

	function xmlrpcOutXML($xml) {
		$xml = encodeconvert('UTF-8', $xml, 1);
		$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n".$xml;
		header('Connection: close');
		header('Content-Length: '.strlen($xml));
		header('Content-Type: text/xml');
		header('Date: '.date('r'));
		echo $xml;
		exit ();
	}

	function sendFault($code = 0, $string = 'Error') {
		header('Content-Type: text/xml');
		echo '<methodResponse><fault><value><struct><member><name>faultCode</name><value><i4>';
		echo $code;
		echo '</i4></value></member><member><name>faultString</name><value><string>';
		echo $string;
		echo '</string></value></member></struct></value></fault></methodResponse>';
		exit ();
	}
	
	function convertDate($time) {
		if (is_numeric($time)) {
			$ymd = sgmdate($time, 'Ymd');
			$hms = sgmdate($time, 'H:i:s');
			return $ymd.'T'.$hms;			
		} else {
			$year = substr($time, 0, 4);
			$month = substr($time, 4, 2);
			$day = substr($time, 6, 2);
			$hour = substr($time, 9, 2);
			$minute = substr($time, 12, 2);
			$second = substr($time, 15, 2);
			$timezone = substr($time, 17);
			return mktime($hour, $minute, $second, $month, $day, $year);
		}
	}
	
	function getUser($username, $password) {
		
		$username = encodeconvert('UTF-8', $username);
		$password = encodeconvert('UTF-8', $password);
		$query = $this->db->query('SELECT * FROM '.tname('members').' WHERE username=\''.$username.'\' AND password=\''.md5($password).'\'');
		
		if ($member = $this->db->fetch_array($query)) {
			if(getuserspace($member['uid'])) {
				return $member;
			} else {
				$this->sendFault(2, 'No Space');
			}
			
		} else {
			$this->sendFault(1, 'Authoried Error');
		}
	}

	function getUserBlog($key, $username, $password) {
		
		$struct = array ();
		$member = $this->getUser($username, $password);
		$blogName = $this->db->result($this->db->query('SELECT spacename FROM '.tname('userspaces').' WHERE uid=\''.$member['uid'].'\''), 0);
		$struct[] = array (
			'url' => geturl('uid/'.$member['uid'], 1), 
			'blogid' => 'blog', 
			'blogName' => $blogName
		);
		
		return $struct;
	}
	
	function newPost($blogid, $username, $password, $post, $publish = true) {

		$member = $this->getUser($username, $password);
		$post['title'] = encodeconvert('UTF-8', $post['title']);
		$post['title'] = trim(shtmlspecialchars($post['title']));
		if (strlen($post['title']) < 2 || strlen($post['title']) > 80) {
			$this->sendFault(101, 'Title Error');
		}
		if (empty($post['categories'])) {
			$query = $this->db->query('SELECT catid FROM '.tname('categories').' WHERE type=\'blog\' LIMIT 0, 1');
		} else {
			$post['categories'] = encodeconvert('UTF-8', $post['categories'][0]);
			$query = $this->db->query('SELECT catid FROM '.tname('categories').' WHERE type=\'blog\' AND name=\''.$post['categories'].'\'');
		}
		$post['categories'] = $this->db->result($query, 0);
		$post['description'] = encodeconvert('UTF-8', $post['description']);
		
		$censormod = false;
		if (empty($post['dateCreated'])) {
			$post['dateCreated'] = time();
		}
		$setsqlarr = array(
			'catid' => $post['categories'],
			'subject' => $post['title'],
			'allowreply' => 1,
			'type' => 'blog',
			'uid' => $member['uid'],
			'username' => addslashes($member['username']),
			'dateline' => $this->timestamp,
			'lastpost' => $this->timestamp
		);
		if ($publish) {
			$setsqlarr['folder'] = 1;
		} else {
			$setsqlarr['folder'] = 2;
		}
		$itemid = inserttable('spaceitems', $setsqlarr, 1);
		$this->db->query('UPDATE '.tname('userspaces').' SET lastpost=\''.$this->timestamp.'\', spaceallnum=spaceallnum+1, spaceblognum=spaceblognum+1 WHERE uid=\''.$member['uid'].'\'');
		$setsqlarr = array(
			'message' => $post['description']
		);
		$setsqlarr['itemid'] = $itemid;
		inserttable('spaceblogs', $setsqlarr);
		if (!empty($post['mt_tb_ping_urls'])) {
			ping($post['mt_tb_ping_urls'], $post['title'], geturl('uid/'.$member['uid'].'/action/viewspace/itemid/'.$itemid, 1), strip_tags($post['description']), $member['username']);
		}
		clearspacecache($member['uid'], 'newblog');
		return (string)$itemid;
	}

	function editPost($postid, $username, $password, $post, $publish = true) {

		$member = $this->getUser($username, $password);
		if (!xml_checkitemuser($postid, $member)) {
			$this->sendFault(201, 'Not Your Blog');
		}
		$post['title'] = encodeconvert('UTF-8', $post['title']);
		$post['title'] = trim(shtmlspecialchars($post['title']));
		if (strlen($post['title']) < 2 || strlen($post['title']) > 80) {
			$this->sendFault(101, 'Title Error');
		}
		if (empty($post['categories'])) {
			$query = $this->db->query('SELECT catid FROM '.tname('categories').' WHERE type=\'blog\' LIMIT 0, 1');
		} else {
			$post['categories'] = encodeconvert('UTF-8', $post['categories'][0]);
			$query = $this->db->query('SELECT catid FROM '.tname('categories').' WHERE type=\'blog\' AND name=\''.$post['categories'].'\'');
		}
		$post['categories'] = $this->db->result($query, 0);
		$post['description'] = encodeconvert('UTF-8', $post['description']);
		if ($member['groupid'] != 1) {
			$post['description'] = checkhtml($post['description']);
		}
		$setsqlarr = array(
			'catid' => $post['categories'],
			'subject' => $post['title'],
			'allowreply' => 1,
			'type' => 'blog',
			'uid' => $member['uid'],
			'username' => addslashes($member['username']),
			'lastpost' => $this->timestamp
		);
		if ($publish) {
			$setsqlarr['folder'] = 1;
		} else {
			$setsqlarr['folder'] = 2;
		}
		updatetable('spaceitems', $setsqlarr, array('itemid'=>intval($postid)));

		$setsqlarr = array(
			'message' => $post['description']
		);
		updatetable('spaceblogs', $setsqlarr, array('itemid'=>intval($postid)));
		clearspacecache($member['uid'], 'newblog');
		return (boolean)true;
	}
	
	function getCategories($blogid, $username, $password) {

		$struct = array ();
		$query = $this->db->query('SELECT catid, name FROM '.tname('categories').' WHERE type=\'blog\'');
		while ($cats = $this->db->fetch_array($query)) {
			$struct[] = array (
				'description' => $cats['name'],
				'htmlUrl' => geturl('action/category/catid/'.$cats['catid'], 1), 
				'rssUrl' => '',
				'title' => $cats['name'],
				'categoryid' => $cats['catid']
			);
		}
		return $struct;
	}

	function getPost($postid, $username, $password) {
		$struct = array();
		$member = $this->getUser($username, $password);
		$query = $this->db->query('SELECT i.itemid, i.uid, i.subject, i.dateline, i.allowreply, c.name, ii.message FROM '.tname('categories').' c LEFT JOIN '.tname('spaceitems').' i ON i.catid=c.catid LEFT JOIN '.tname('spaceblogs').' ii ON ii.itemid=i.itemid WHERE i.itemid=\''.intval($postid).'\' AND i.uid=\''.$member['uid'].'\' AND i.type=\'blog\'');
		$item = $this->db->fetch_array($query);
		$date = new stdClass();
		$date->date = $this->convertDate($item['dateline']);
		$date->is_date = true;
		$struct = array(
			'userid' => $item['uid'],
			'dateCreated' => $date,
			'datePosted' => $date,
			'dateModified' => $date,
			'title' => $item['subject'],
			'postid' => $item['itemid'],
			'categories' => array($item['name']),
			'link' => geturl('uid/'.$item['uid'].'/action/viewspace/itemid/'.$item['itemid'], 1),
			'permalink' => geturl('uid/'.$item['uid'].'/action/viewspace/itemid/'.$item['itemid'], 1),
			'description' => $item['message'],
			'content' => $item['message'],
			'mt_allow_comments' => $item['allowreply'],
			'mt_allow_pings' => true,
			'mt_excerpt' => ''
		);
		return $struct;	
	}
	
	function getRecentPosts($blogid, $username, $password, $num) {
		$struct = array();
		$member = $this->getUser($username, $password);
		$query = $this->db->query('SELECT i.itemid, i.uid, i.subject, i.dateline, i.allowreply, c.name, ii.message FROM '.tname('categories').' c LEFT JOIN '.tname('spaceitems').' i ON i.catid=c.catid LEFT JOIN '.tname('spaceblogs').' ii ON ii.itemid=i.itemid  WHERE i.uid=\''.$member['uid'].'\' AND i.type=\'blog\' ORDER BY i.dateline DESC LIMIT 0, '.intval($num));
		while($item = $this->db->fetch_array($query)) {
			$date = new stdClass();
			$date->date = $this->convertDate($item['dateline']);
			$date->is_date = true;
			$struct[] = array(
				'userid' => $item['uid'],
				'dateCreated' => $date,
				'datePosted' => $date,
				'dateModified' => $date,
				'title' => $item['subject'],
				'postid' => $item['itemid'],
				'categories' => array($item['name']),
				'link' => geturl('uid/'.$item['uid'].'/action/viewspace/itemid/'.$item['itemid'], 1),
				'permalink' => geturl('uid/'.$item['uid'].'/action/viewspace/itemid/'.$item['itemid'], 1),
				'description' => $item['message'],
				'content' => $item['message'],
				'mt_allow_comments' => $item['allowreply'],
				'mt_allow_pings' => true,
				'mt_excerpt' => ''
			);
		}
		return $struct;	
	}
	
}
?>