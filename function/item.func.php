<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	信息管理函数

	$RCSfile: item.func.php,v $
	$Revision: 1.31 $
	$Date: 2007/04/30 14:51:57 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

//链接处理
function initspaceitemurl() {
	global $_SGET, $newurl, $theurl;

	$_SGET['op'] = trim(postget('op'));
	$_SGET['page'] = intval(postget('page'));
	$_SGET['catid'] = intval(postget('catid'));
	$_SGET['itemtypeid'] = intval(postget('itemtypeid'));
	$_SGET['folder'] = intval(postget('folder'));
	if(empty($_SGET['subtype'])) $_SGET['subtype'] = '';
	($_SGET['page']<1)?$_SGET['page']=1:'';
	$newurl = $theurl.'&op='.$_SGET['op'].'&catid='.$_SGET['catid'].'&itemtypeid='.$_SGET['itemtypeid'].'&folder='.$_SGET['folder'].'&subtype='.$_SGET['subtype'];
	
	if(!empty($_GET['openwindow'])) setcookie('_openwindow', 1);
	if(!empty($_COOKIE['_openwindow'])) {
		$_SGET['openwindow'] = 1;
	} else {
		$_SGET['openwindow'] = 0;
	}
}

//获取带分页的列表
function selectitemlist($type, $perpage, $newurl, $showdetail=0) {
	global $_SGLOBAL, $_SGET;
	
	$rtarr = array();
	$wheresqlarr = array();
	$wheresqlarr['i.uid'] = $_SGLOBAL['supe_uid'];
	$wheresqlarr['i.type'] = $type;
	if(!empty($_SGET['catid'])) {
		$wheresqlarr['i.catid'] = $_SGET['catid'];
	}
	if(!empty($_SGET['itemtypeid'])) {
		$wheresqlarr['i.itemtypeid'] = $_SGET['itemtypeid'];
	}
	if(!empty($_SGET['folder'])) {
		$wheresqlarr['i.folder'] = $_SGET['folder'];
	}
	if(!empty($_SGET['digest'])) {
		$wheresqlarr['i.digest'] = $_SGET['digest'];
	}
	if(!empty($_SGET['subtype'])) {
		$wheresqlarr['i.subtype'] = $_SGET['subtype'];
	}
	$wheresqlstr = getwheresql($wheresqlarr);
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' i WHERE '.$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	$multipage = '';
	$listarr = array();
	if($listcount) {
		if(empty($_SGET['order'])) {
			$order = 'i.top DESC, i.dateline DESC';
		} else {
			$order = 'i.'.$_SGET['order'].' '.$_SGET['sc'];
		}
		$start = ($_SGET['page']-1)*$perpage;
		
		if($showdetail) {
			$querystr = 'SELECT ii.*, i.* FROM '.tname('spaceitems').' i LEFT JOIN '.tname(gettypetablename($type)).' ii ON ii.itemid=i.itemid WHERE '.$wheresqlstr.' ORDER BY '.$order.' LIMIT '.$start.','.$perpage;
		} else {
			$querystr = 'SELECT i.* FROM '.tname('spaceitems').' i WHERE '.$wheresqlstr.' ORDER BY '.$order.' LIMIT '.$start.','.$perpage;
		}
		
		$query = $_SGLOBAL['db']->query($querystr);
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			$listarr[] = $item;
		}
		$multipage = multi($listcount, $perpage, $_SGET['page'], $newurl);
	}
	
	$rtarr['listcount'] = $listcount;
	$rtarr['multipage'] = $multipage;
	$rtarr['listarr'] = $listarr;
	
	return $rtarr;
}

//获取详细内容
function getitemmsg($tablename, $itemid, $getattach=1) {
	global $_SGLOBAL;

	$thevalue = array();
	$query = $_SGLOBAL['db']->query('SELECT item.*, msg.* FROM '.tname('spaceitems').' item LEFT JOIN '.tname($tablename).' msg ON item.itemid=msg.itemid WHERE item.itemid=\''.intval($itemid).'\' AND item.uid=\''.$_SGLOBAL['supe_uid'].'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		
		if(empty($thevalue['itemid'])) return showmessage('operation_of_the_info_can_not_be_selected');
		
		//获取TAG
		$thevalue['tagarr'] = array();
		$query = $_SGLOBAL['db']->query('SELECT t.tagname FROM '.tname('spacetags').' st LEFT JOIN '.tname('tags').' t ON t.tagid=st.tagid WHERE st.itemid=\''.$itemid.'\'');
		while ($itemtag = $_SGLOBAL['db']->fetch_array($query)) {
			if(!empty($itemtag['tagname'])) $thevalue['tagarr'][md5($itemtag['tagname'])] = $itemtag['tagname'];
		}
		$thevalue['tagname'] = implode(' ', $thevalue['tagarr']);
		
		//附件
		if(empty($thevalue['hash'])) $thevalue['hash'] = getattachhash();
		$thevalue['uploadarr'] = array();
		if($getattach && !empty($thevalue['haveattach'])) {
			$query = $_SGLOBAL['db']->query('SELECT a.* FROM '.tname('attachments').' a WHERE a.itemid=\''.$itemid.'\' ORDER BY a.aid');
			while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
				$thevalue['uploadarr'][] = $attach;
			}
			if(empty($thevalue['uploadarr'])) {
				updatetable('spaceitems', array('haveattach' => 0), array('itemid' => $itemid));
			}
		}
	}
	return $thevalue;
}

//获取上传临时码
function getattachhash() {
	global $_SGLOBAL;
	$str = $_SGLOBAL['supe_uid'].'/'.$_SGLOBAL['timestamp'];
	return smd5($str);
}

//获取编辑器显示代码
function getedit($name, $message, $op=0) {
	
	$message = str_replace(array("\r", "\n"), array('\r', '\n'), addcslashes($message, '/"\\'));

	$html = '<div id="fulledit" style="width: 100%;">
				<div id="'.$name.'" style="width: 100%;"></div>
			</div>
			<script type="text/javascript">
			et = new word("'.$name.'", "'.$message.'", "", '.$op.');
			</script>';

	return $html;
}

//获取上传显示代码
function getupload($name, $attacharr, $noinsert, $allowtype, $allowmax, $thumbarr=array(100,100)) {
	global $_SGLOBAL, $slang;

	$thetext = '';
	$count = count($attacharr);
	if(empty($noinsert)) {
		$noinsert = 0;
		$inserthtml = getuploadinserthtml($attacharr);
	} else {
		$inserthtml = getuploadinserthtml($attacharr, 1);
	}
	$promptstr = $allowtype;
	if(empty($promptstr)) {
		$query = $_SGLOBAL['db']->query('SELECT fileext FROM '.tname('attachmenttypes'));
		while ($type = $_SGLOBAL['db']->fetch_array($query)) {
			$types[$type['fileext']] = $type['fileext'];
		}
		$promptstr = implode(',', $types);
	}
	
	$thetext .= '<div id="divshowupload">'.$inserthtml.'</div>';

	$thetext .= '
	<div id="uploadbox">
	<div class="tabs">
	<a id="localuploadtab" href="javascript:;" onclick="hideshowtags(\'uploadbox\', \'localupload\');" class="current">'.$slang['local_upload'].'</a>
	<a id="remoteuploadtab" href="javascript:;" onclick="hideshowtags(\'uploadbox\', \'remoteupload\');">'.$slang['remote_upload'].'</a>';
	if($allowmax > 1) {
		$thetext .= '<a id="batchuploadtab" href="javascript:;" onclick="hideshowtags(\'uploadbox\', \'batchupload\');">'.$slang['batch_upload'].'</a>';
	}
	$thetext .= '</div>
	<div id="localupload">
	<table summary="" cellpadding="0" cellspacing="6" border="0" width="100%">
	<tr>
	<td><label for="localfile">'.$slang['options_paper'].'</label></td>
	<td><input name="localfile" type="file" id="localfile" size="28" /><div id="localfiletips" class="tips">'.$slang['upload_types_allowed'].':'.$promptstr.'</div></td>
	</tr>
	<tr>
	<td><label for="uploadsubject0">'.$slang['craft_shows'].'</label></td>
	<td><input name="uploadsubject0" type="text" size="40" />
	<button onclick="return uploadFile(0)">'.$slang['upload'].'</button></td>
	</tr></table>
	</div>
	<div id="remoteupload" style="display: none;">
	<table summary="" cellpadding="0" cellspacing="6" border="0" width="100%">
	<tr>
	<td><label for="remotefile">'.$slang['import_addresses'].'</lable></td>
	<td><input type="text" size="40" name="remotefile" id="remotefile" value="http://" /><div id="remotefiletips" class="tips">'.$slang['upload_types_allowed'].':'.$promptstr.'</div></td>
	<td rowspan="2" class="upbtntd"></td>
	</tr>
	<tr>
	<td><label for="uploadsubject1">'.$slang['briefly'].'</lable></td>
	<td><input id="uploadsubject1" name="uploadsubject1" type="text" size="40" />
	<button onclick="return uploadFile(1)">'.$slang['upload'].'</button></td>
	</tr></table>
	</div>
	<div id="batchupload" style="display: none;">
	<table summary="" cellpadding="0" cellspacing="6" border="0" width="100%">
	<tr>
	<td>
	<button id="doupfile" onclick="return uploadFile(2)" style="float: right;">'.$slang['upload'].'</button>
	<span id="batchdisplay"><input class="fileinput" id="batch_1" name="batchfile[]" onchange="insertimg(this)" type="file" /><div id="batch_1tips" class="tips">'.$slang['upload_types_allowed'].':'.$promptstr.'</div></span>
	</td>
	<tr>
	<td>
	<div id="batchpreview"></div>
	</td>
	</tr>
	<tr>
	<td>
	<button id="delall" name="delall" onclick="return delpic()" style="opacity: 0.5; filter: alpha(opacity=50);" >'.$slang['all_emptied'].'</button>
	</td>
	</tr></table>
	</div>
	<p class="textmsg" id="divshowuploadmsg" style="display:none"></p>
	<p class="textmsg succ" id="divshowuploadmsgok" style="display:none"></p>
	<input type="hidden" id="uploadallowmax" name="uploadallowmax" value="'.$allowmax.'">
	<input type="hidden" name="uploadallowtype" value="'.$allowtype.'">
	<input type="hidden" name="thumbwidth" value="'.$thumbarr[0].'">
	<input type="hidden" name="thumbheight" value="'.$thumbarr[1].'">
	<input type="hidden" name="noinsert" value="'.$noinsert.'">';

	return $thetext;
}

//获取预定义字段信息
function getprefield($type) {
	global $_SGLOBAL;
	
	$rarr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('prefields').' WHERE type=\''.$type.'\'');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$rarr[$value['field']][] = $value;
	}
	return $rarr;
}

//获取自定义字段
function customfieldhtml($thevalue) {
	global $_SGLOBAL, $slang;
	
	$type = $thevalue['type'];
	$cfhtmlselect = array('0'=> $slang['do_not_use']);

	$results = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('customfields')." WHERE (uid='".saddslashes($_SGLOBAL['supe_uid'])."' OR isshare='1') AND type='".saddslashes($type)."'  ORDER BY displayorder");
	while ($r_array = $_SGLOBAL['db']->fetch_array($query)) {
		$results[] = $r_array;
	}
	$cfarr = $results;
	
	$cfhtml = '';
	$tbodynum = 0;
	foreach ($cfarr as $cfkey => $cfvalue) {
		if(empty($thevalue['customfieldid'])) {
			if($cfvalue['isdefault']) {
				$thevalue['customfieldid'] = $cfvalue['customfieldid'];
			}
		}
		$cfhtmlselect[$cfvalue['customfieldid']] = $cfvalue['name'];
		$cfarr = unserialize($cfvalue['customfieldtext']);
		if(is_array($cfarr) && $cfarr) {
			if(!empty($thevalue['customfieldid']) && $thevalue['customfieldid'] == $cfvalue['customfieldid']) {
				$tbodydisplay = '';
				if(empty($thevalue['customfieldtext'])) {
					$thecfarr = array();
				} else {
					$thecfarr = unserialize($thevalue['customfieldtext']);
				}
			} else {
				$tbodydisplay = 'none';
				$thecfarr = array();
			}
			$tbodynum++;
			$cfhtml .= '<tbody id="cf_'.$tbodynum.'" style="display:'.$tbodydisplay.'">';
			
			foreach ($cfarr as $ckey => $cvalue) {
				$inputstr = '';
				if(empty($thecfarr[$ckey])) $thecfarr[$ckey] = '';
				$cfoptionarr = array();
				if($cvalue['type'] == 'select' || $cvalue['type'] == 'checkbox') {
					$cfoptionstr = $cvalue['option'];
					$coarr = explode("\n", $cfoptionstr);
					$coarr = sarray_unique($coarr);
					foreach ($coarr as $covalue) {
						$covalue = trim($covalue);
						$cfoptionarr[$covalue] = $covalue;
					}
				}
				switch ($cvalue['type']) {
					case 'input':
						$inputstr = '<input name="customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']" type="text" size="30" value="'.$thecfarr[$ckey].'" />';
						break;
					case 'textarea':
						$inputstr = '<textarea name="customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']" rows="5" cols="60">'.$thecfarr[$ckey].'</textarea>';
						break;
					case 'select':
						$inputstr = getselectstr('customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']', $cfoptionarr, $thecfarr[$ckey]);
						break;
					case 'checkbox':
						$inputstr = getcheckboxstr('customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']', $cfoptionarr, $thecfarr[$ckey]);
						break;
				}
				$cfhtml .= '<tr><th>'.$cvalue['name'].'</th><td>'.$inputstr.'</td></tr>';
			}
			$cfhtml .= '</tbody>';
		}
	}
	
	$cfhtml = '<tr><th>'.$slang['custom_field'].'</th>
	<td>'.getselectstr('customfieldid', $cfhtmlselect, $thevalue['customfieldid'], 'onchange="showdivcustomfieldtext()"').'
	<p>'.$slang['custom_field_note'].' <a href="spacecp.php?action=customfields&type='.$type.'" target="_blank">'.$slang['manage_custom_field'].'</a></p>
	</td></tr>
	'.$cfhtml;

	$jscftext = '
	<script language="javascript">
	<!--
	function showdivcustomfieldtext() {
		var cfindex = document.getElementById("customfieldid").selectedIndex;
		showtbody(cfindex);
	}	
	function showtbody(id) {
		for(i=1;i<='.$tbodynum.';i++){
			obj=document.getElementById("cf_"+i);
			if(i == id) {
				obj.style.display="";
			} else {
				obj.style.display="none";
			}
		}
	}
	//-->
	</script>
	';
	
	$result = array('js'=>$jscftext, 'html'=>$cfhtml);
	return $result;
}

//对发布的tag进行检查
function posttagcheck($tagname) {
	global $lang;
	
	$tagname = trim(strtolower($tagname));
	if(strlen($tagname) < 2 || strlen($tagname) > 15 || preg_match("/($lang[tag_match])/", $tagname)) {
		return '';
	} else {
		return $tagname;
	}
}

//获取相关TAG
function postgetincludetags($message, $tagnamearr) {
	global $_SGLOBAL;
	
	$postincludetags = '';
	if(!file_exists(S_ROOT.'./data/system/tag.cache.php')) {
		include_once(S_ROOT.'./include/cron/tagcontent.php');
	}
	@include_once(S_ROOT.'./data/system/tag.cache.php');
	if(empty($_SGLOBAL['tagcontent'])) $_SGLOBAL['tagcontent'] = '';
	$tagtext = implode('|', $tagnamearr).'|'.$_SGLOBAL['tagcontent'];
	$postincludetags = getincluetags($message, $tagtext);
	return $postincludetags;
}

//获取内容中包含的TAG
function getincluetags($text, $tagtext) {
	$resultarr = array();
	$tagtext = str_replace('/', '\/', $tagtext);
	preg_match_all("/($tagtext)/", $text, $matches);
	if(!empty($matches[1]) && is_array($matches[1])) {
		foreach ($matches[1] as $value) {
			if(strlen($value)>2) $resultarr[$value] = $value;
		}
	}
	return implode("\t", $resultarr);
}

//检查选择的信息是否正确
function checkselectitem($pitems, $type, $uid) {
	global $_SGLOBAL, $itemarr, $itemidarr, $itemidstr;
	
	if(!empty($pitems)) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE itemid IN ('.simplode($pitems).')');
		while ($item = $_SGLOBAL['db']->fetch_array($query)) {
			if($item['uid'] == $uid && $item['type'] == $type) {
				$itemarr[] = $item;
				$itemidarr[] = $item['itemid'];
			}
		}
	}
	if(empty($itemidarr)) {
		showmessage('please_select_the_operating_information');
	} else {
		$itemidstr = simplode($itemidarr);
	}

}

//检查内容中屏蔽词语
function messagecensor($message) {
	global $censormod, $postfolder, $_DCACHE;
	
	@include_once(S_ROOT.'/data/system/bbs_censor.cache.php');
	if(!empty($_DCACHE['censor']) && is_array($_DCACHE['censor'])) {
		if($_DCACHE['censor']['banned'] && preg_match($_DCACHE['censor']['banned'], $message)) {
			showmessage('words_can_not_publish_the_shield');
		}
		if($_DCACHE['censor']['mod'] && preg_match($_DCACHE['censor']['mod'], $message)) {
			$postfolder = 3;
			$censormod = true;
		}
		if(!empty($_DCACHE['censor']['filter'])) {
			$message = @preg_replace($_DCACHE['censor']['filter']['find'], $_DCACHE['censor']['filter']['replace'], $message);
		}
	}
	
	return $message;
}

//处理个人分类
function postitemtype($itemtypeid, $type) {
	global $_SGLOBAL;
	
	$itemtypearr = array('typeid'=>0, 'typename'=>'');
	$itemtypeid = shtmlspecialchars($itemtypeid);
	$query = $_SGLOBAL['db']->query('SELECT typeid, typename FROM '.tname('itemtypes').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND typeid=\''.$itemtypeid.'\' AND type=\''.$type.'\'');
	if($itemtypearr = $_SGLOBAL['db']->fetch_array($query)) {
	} else {
		$query = $_SGLOBAL['db']->query('SELECT typeid, typename FROM '.tname('itemtypes').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND typename=\''.$itemtypeid.'\' AND type=\''.$type.'\'');
		$itemtypearr = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($itemtypearr)) {
		$setsqlarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'type' => $type,
			'typename' => $itemtypeid
		);
		$itemtypearr['typeid'] = inserttable('itemtypes', $setsqlarr, 1);
		$itemtypearr['typename'] = stripslashes($itemtypeid);

		//更新个人分类缓存
		clearspacecache($_SGLOBAL['supe_uid'], 'itemtype');
	}

	return $itemtypearr;
}

//处理归类输入的TAG
function posttag($tagnamestr) {
	global $_SGLOBAL, $lang;

	$tagarr = array('existsname'=>array(), 'nonename'=>array(), 'closename'=>array(), 'existsid'=>array());
	if(empty($tagnamestr)) return $tagarr;

	$tagnamearr = array();
	$valuearr = explode(' ', str_replace(',', ' ', shtmlspecialchars($tagnamestr)));
	
	foreach ($valuearr as $value) {
		if(count($tagnamearr) > 10) break;
		$value = posttagcheck($value);
		if($value) $tagnamearr[md5(strtolower($value))] = $value;
	}
	if(empty($tagnamearr)) return $tagarr;

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('tags').' WHERE tagname IN ('.simplode($tagnamearr).')');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagarr['existsid'][] = $value['tagid'];
		$tagarr['existsname'][] = $value['tagname'];
		if($value['close']) $tagarr['closename'][] = $value['tagname'];
	}

	if(!empty($tagarr['existsname'])) {
		foreach ($tagnamearr as $value) {
			if(!in_array($value, $tagarr['existsname'])) {
				$tagarr['nonename'][] = $value;
			}
		}
	} else {
		$tagarr['nonename'] = $tagnamearr;
	}
	
	if(!empty($tagarr['closename'])) {
		showmessage($lang['not_allowed_to_belong_to_the_following_tag'].':<p>'.implode(',', $tagarr['closename']).'</p>');
	}
	return $tagarr;
}

//信息TAG关联处理
function postspacetag($op, $type, $itemid, $tagarr) {
	global $_SGLOBAL;

	$colnumname = "space{$type}num";
	$deletetagidarr = $addtagidarr = $spacetagidarr = array();
	if($op == 'add') {
		if(!empty($tagarr['existsid'])) {
			$addtagidarr = $tagarr['existsid'];
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spaceallnum=spaceallnum+1, '.$colnumname.'='.$colnumname.'+1 WHERE tagid IN ('.simplode($tagarr['existsid']).')');
		}
	} else {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacetags').' WHERE itemid=\''.$itemid.'\'');
		while ($spacetag = $_SGLOBAL['db']->fetch_array($query)) {
			if(!empty($tagarr['existsid']) && in_array($spacetag['tagid'], $tagarr['existsid'])) {
				$spacetagidarr[] = $spacetag['tagid'];
			} else {
				$deletetagidarr[] = $spacetag['tagid'];
			}
		}
		foreach ($tagarr['existsid'] as $etagid) {
			if(!empty($spacetagidarr) && in_array($etagid, $spacetagidarr)) {
			} else {
				$addtagidarr[] = $etagid;
			}
		}
		if(!empty($deletetagidarr)) {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE itemid='.$itemid.' AND tagid IN ('.simplode($deletetagidarr).')');
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spaceallnum=spaceallnum-1, '.$colnumname.'='.$colnumname.'-1 WHERE tagid IN ('.simplode($deletetagidarr).')');
		}
		if(!empty($addtagidarr)) {
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET spaceallnum=spaceallnum+1, '.$colnumname.'='.$colnumname.'+1 WHERE tagid IN ('.simplode($addtagidarr).')');
		}
	}
	//TAG
	if(!empty($tagarr['nonename'])) {
		foreach ($tagarr['nonename'] as $posttagname) {
			$insertsqlarr = array(
				'tagname' => $posttagname,
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'dateline' => $_SGLOBAL['timestamp'],
				'spaceallnum' => 1,
				$colnumname => 1
			);
			$addtagidarr[] = inserttable('tags', $insertsqlarr, 1);			
		}
	}
	if(!empty($addtagidarr)) {
		$insertstr = $comma = '';
		foreach ($addtagidarr as $tagid) {
			$insertstr .= $comma.'(\''.$itemid.'\',\''.$tagid.'\',\''.$_SGLOBAL['timestamp'].'\',\''.$type.'\')';
			$comma = ',';
		}
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('spacetags').' (itemid, tagid, dateline, type) VALUES '.$insertstr);
	}
}

//获取相关信息ID
function getrelativeitemids($itemid, $typearr=array(), $num=10) {
	global $_SGLOBAL;

	$tagidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('spacetags')." WHERE itemid='$itemid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagidarr[] = $value['tagid'];
	}
	if(empty($tagidarr)) return '';
	
	$sqlplus = '';
	if(!empty($typearr)) $sqlplus = "AND type IN (".simplode($typearr).")";
	$itemidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spacetags')." WHERE tagid IN (".simplode($tagidarr).") AND itemid<>'$itemid' $sqlplus ORDER BY itemid DESC LIMIT 0, $num");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemidarr[] = $value['itemid'];
	}
	return implode(',', $itemidarr);
	
}

//检查是否为本人信息
function checkitemuser() {
	global $_SGLOBAL, $itemid, $_GET, $_POST;
	
	$itemid = $_GET['itemid'] = $_POST['itemid'] = intval(postget('itemid'));
	if(!empty($itemid)) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE itemid='$itemid' AND uid='$_SGLOBAL[supe_uid]'");
		if(!$_SGLOBAL['theitem'] = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('you_no_authority_to_operate');
		}
	}
}

//提交数据通用处理
function postcheck() {
	global $_SGLOBAL, $_SCONFIG, $itemid, $_POST, $censormod, $itemtypearr, $setcustomfieldtext, $tagarr, $setsqlarr, $postfolder, $type;

	//输入检查
	$itemid = intval($_POST['itemid']);
	$_POST['tid'] = empty($_POST['tid'])?0:intval($_POST['tid']);
	$_POST['catid'] = intval($_POST['catid']);
	$_POST['itemtypeid'] = trim($_POST['itemtypeid']);
	$_POST['digest'] = intval($_POST['digest']);
	$_POST['top'] = intval($_POST['top']);
	$_POST['customfieldid'] = intval($_POST['customfieldid']);
	$_POST['gid'] = empty($_POST['gid'])?0:intval($_POST['gid']);
	
	//检查输入
	$_POST['subject'] = trim(shtmlspecialchars($_POST['subject']));
	
	//utf-8编码问题
	if($_SCONFIG['charset'] == 'utf-8') {
		$subjectlen = strlen(utf8_decode($_POST['subject']));
	} else {
		$subjectlen = strlen($_POST['subject']);
	}
	if($subjectlen < 2 || $subjectlen > 80) {
		showmessage('space_suject_length_error');
	}
	if(empty($_POST['catid'])) {
		showmessage('please_choose_classification_system');
	}
	
	//检查屏蔽词语
	$censormod = false;
	$_POST['subject'] = messagecensor($_POST['subject']);//标题屏蔽
	if(strlen($_POST['message']) > 1) {
		$_POST['message'] = messagecensor($_POST['message']);
	}
	
	//个人分类
	$itemtypearr = array('typeid'=>0, 'typename'=>'');
	if(!empty($_POST['itemtypeid'])) {
		$itemtypearr = postitemtype($_POST['itemtypeid'], $type);
	}

	//自定义信息
	$setcustomfieldtext = empty($_POST['customfieldtext'][$_POST['customfieldid']])?serialize(array()):addslashes(serialize(shtmlspecialchars(sstripslashes($_POST['customfieldtext'][$_POST['customfieldid']]))));

	//TAG处理
	$tagarr = posttag($_POST['tagname']);
	
	//构建数据
	if(empty($_POST['hash'])) {
		$_POST['hash'] = '';
	} else {
		$_POST['hash'] = preg_replace("/[^a-z0-9\-\_]/i", '', $_POST['hash']);
	}
	$setsqlarr = array(
		'catid' => $_POST['catid'],
		'itemtypeid' => intval($itemtypearr['typeid']),
		'subject' => $_POST['subject'],
		'digest' => $_POST['digest'],
		'top' => $_POST['top'],
		'folder' => empty($postfolder)?1:$postfolder,
		'gid' => $_POST['gid'],
		'hash' => empty($_POST['hash'])?'':$_POST['hash']
	);
	
	//允许评论
	if(!empty($_POST['allowreply'])) {
		$setsqlarr['allowreply'] = 1;
	} else {
		$setsqlarr['allowreply'] = 0;
	}
	
	//圈子
	if($_POST['gid']) {
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('groupuid').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND gid=\''.$_POST['gid'].'\''), 0);
		if(empty($count)) {
			$setsqlarr['gid'] = 0;
		} else {
			updatetable('groups', array('lastpost'=>$_SGLOBAL['timestamp']), array('gid'=>$setsqlarr['gid']));
		}
	}
	
	//子分类
	if(empty($_POST['subtype']) || !in_array($_POST['subtype'], array('media','real','flash','auction','goods'))) $_POST['subtype'] = '';

}

//获取系统分类、个人分类和圈子
function getaddstr($thevalue, $getgroup=1) {
	global $_SGLOBAL, $catstr, $itemtypestr, $groupstr, $type, $uplistarr, $_GET;

	$catstr = $itemtypestr = $groupstr = '';
	
	$thevalue['catid'] = empty($thevalue['catid'])?0:intval($thevalue['catid']);
	$thevalue['itemtypeid'] = empty($thevalue['itemtypeid'])?0:intval($thevalue['itemtypeid']);
	$thevalue['gid'] = empty($thevalue['gid'])?0:intval($thevalue['gid']);
	
	//系统分类
	$uplistarr = getcategory($type);
	$catselarr = array($thevalue['catid']=>' selected');
	foreach ($uplistarr as $key => $value) {
		if(empty($catselarr[$key])) $catselarr[$key] = '';
		$catstr .= '<option value="'.$key.'"'.$catselarr[$key].'>'.$value['pre'].$value['name'].'</option>';
	}

	//个人分类
	$itemtypearr = getitemtype($type);
	$itemtypeselarr = array($thevalue['itemtypeid']=>' selected');
	foreach ($itemtypearr as $key => $value) {
		if(empty($itemtypeselarr[$key])) $itemtypeselarr[$key] = '';
		$itemtypestr .= '<option value="'.$key.'"'.$itemtypeselarr[$key].'>'.$value.'</option>';
	}

	//圈子
	if($getgroup) {
		if(empty($thevalue['gid']) && !empty($_GET['gid'])) $thevalue['gid'] = $_GET['gid'];
		$grouparr = getgroups();
		$groupselarr = array($thevalue['gid']=>' selected');
		foreach ($grouparr as $key => $value) {
			if(empty($groupselarr[$key])) $groupselarr[$key] = '';
			$groupstr .= '<option value="'.$key.'"'.$groupselarr[$key].'>'.$value.'</option>';
		}
	}

}

?>