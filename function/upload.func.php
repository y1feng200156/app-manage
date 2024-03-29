<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	上传处理函数

	$RCSfile: upload.func.php,v $
	$Revision: 1.22 $
	$Date: 2007/05/10 15:06:34 $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function savelocalfile($filearr, $thumbarr=array(100, 100), $objfile='') {
	
	global $_SCONFIG, $_SGLOBAL;
	
	$patharr = $deault = array('file'=>'', 'thumb'=>'', 'name'=>'', 'type'=>'', 'size'=>0);
	
	//debug 传入参数
	$filename = strip_tags($filearr['name']);
	$tmpname = str_replace('\\', '\\\\', $filearr['tmp_name']);

	//debug 文件后缀
	$ext = fileext($filename);
	
	$patharr['name'] = addslashes($filename);
	$patharr['type'] = $ext;
	$patharr['size'] = $filearr['size'];
	
	//debug 文件名
	if($objfile) {
		$newfilename = $objfile;
		$isimage = 0;
		$patharr['file'] = $patharr['thumb'] = $objfile;
	} else {
		if(in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
			$isimage = 1;
		} else {
			$isimage = 0;
			$ext = 'attach';
		}
		if(empty($_SGLOBAL['_num'])) $_SGLOBAL['_num'] = 0;
		$_SGLOBAL['_num'] = intval($_SGLOBAL['_num']);
		$_SGLOBAL['_num']++;
		$filemain = $_SGLOBAL['supe_uid'].'_'.sgmdate($_SGLOBAL['timestamp'], 'YmdHis').$_SGLOBAL['_num'];
		
		//debug 得到存储目录
		$dirpath = getattachdir();
		if(!empty($dirpath)) $dirpath .= '/';
		$patharr['file'] = $dirpath.$filemain.'.'.$ext;
		
		//debug 上传
		$newfilename = A_DIR.'/'.$patharr['file'];
	}
	if(@copy($tmpname, $newfilename)) {
	} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmpname, $newfilename))) {
	} elseif(@rename($tmpname, $newfilename)) {
	} else {
		return $deault;
	}
	@unlink($tmpname);
	
	//debug 缩略图水印
	if($isimage && empty($objfile)) {
		if($ext != 'gif') {
			//debug 缩略图
			$patharr['thumb'] = makethumb($patharr['file'], $thumbarr);
			//debug 加水印
			if(!empty($patharr['thumb'])) makewatermark($patharr['file']);
		}
		if(empty($patharr['thumb'])) $patharr['thumb'] = $patharr['file'];
	}

	return $patharr;
	
}

function saveremotefile($url, $thumbarr=array(100, 100), $mkthumb=1, $maxsize=0) {
	global $_SCONFIG, $_SGLOBAL;
	
	$patharr = $blank = array('file'=>'', 'thumb'=>'', 'name'=>'', 'type'=>'', 'size'=>0);

	$ext = fileext($url);
	$patharr['type'] = $ext;

	if(in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
		$isimage = 1;
	} else {
		$isimage = 0;
		$ext = 'attach';
	}

	//debug 文件名
	if(empty($_SGLOBAL['_num'])) $_SGLOBAL['_num'] = 0;
	$_SGLOBAL['_num'] = intval($_SGLOBAL['_num']);
	$_SGLOBAL['_num']++;
	$filemain = $_SGLOBAL['supe_uid'].'_'.sgmdate($_SGLOBAL['timestamp'], 'YmdHis').$_SGLOBAL['_num'];
	$patharr['name'] = $filemain.'.'.$ext;
	
	//debug 得到存储目录
	$dirpath = getattachdir();
	if(!empty($dirpath)) $dirpath .= '/';
	$patharr['file'] = $dirpath.$filemain.'.'.$ext;
	
	//debug 上传
	$content = sreadfile($url, 'rb', 1, $maxsize);
	if(empty($content)) return $blank;
	
	writefile(A_DIR.'/'.$patharr['file'], $content, 'text', 'wb', 0);
	if(!file_exists(A_DIR.'/'.$patharr['file'])) return $blank;
	
	$patharr['size'] = filesize(A_DIR.'/'.$patharr['file']);
	
	//debug 缩略图水印
	if($isimage) {
		if($mkthumb && $ext != 'gif') {
			//debug 缩略图
			$patharr['thumb'] = makethumb($patharr['file'], $thumbarr);
			//debug 加水印
			if(!empty($patharr['thumb'])) makewatermark($patharr['file']);
		}
		if(empty($patharr['thumb'])) $patharr['thumb'] = $patharr['file'];
	}

	return $patharr;
}

function getattachdir() {
	
	global $_SCONFIG, $_SGLOBAL;

	switch ($_SCONFIG['attachmentdirtype']) {
		case 'year':
			$dirpatharr[] = sgmdate($_SGLOBAL['timestamp'], 'Y');
			break;
		case 'month':
			$dirpatharr[] = sgmdate($_SGLOBAL['timestamp'], 'Y');
			$dirpatharr[] = sgmdate($_SGLOBAL['timestamp'], 'm');
			break;
		case 'day':
			$dirpatharr[] = sgmdate($_SGLOBAL['timestamp'], 'Y');
			$dirpatharr[] = sgmdate($_SGLOBAL['timestamp'], 'm');
			$dirpatharr[] = sgmdate($_SGLOBAL['timestamp'], 'd');
			break;
		case 'md5':
			$md5string = md5($_SGLOBAL['supe_uid'].'-'.$_SGLOBAL['timestamp'].'-'.$_SGLOBAL['_num']);
			$dirpatharr[] =  substr($md5string, 0, 1);
			$dirpatharr[] =  substr($md5string, 1, 1);
			break;
		default:
			break;
	}
	
	$dirs = A_DIR;
	$subarr = array();
	foreach ($dirpatharr as $value) {
		$dirs .= '/'.$value;
		if(smkdir($dirs)) {
			$subarr[] = $value;
		} else {
			break;
		}
	}
	
	return implode('/', $subarr);
}

function smkdir($dirname, $ismkindex=1) {
	$mkdir = false;
	if(!is_dir($dirname)) {
		if(@mkdir($dirname, 0777)) {
			if($ismkindex) {
				@fclose(@fopen($dirname.'/index.htm', 'w'));
			}
			$mkdir = true;
		}
	} else {
		$mkdir = true;
	}
	return $mkdir;
}

function filemain($filename) {
	return trim(substr($filename, 0, strrpos($filename, '.')));
}

function makethumb($srcfile, $thumbsizearr = array(100, 100), $dstfile='') {
	global $_SCONFIG;

	if(empty($dstfile)) {
		$dstfile = filemain($srcfile).'.thumb.jpg';//自建立缩略图
		$srcfile_file = A_DIR.'/'.$srcfile;
		$dstfile_file = A_DIR.'/'.$dstfile;
	} else {
		$srcfile_file = $srcfile;
		$dstfile_file = $dstfile;
	}
	if (!file_exists($srcfile_file)) {
	    return '';
	}

	$opnotkeepscale = 4;
	$opbestresizew = 8;
	$opbestresizeh = 16;
	$option = $_SCONFIG['thumboption'];
	$cutmode = $_SCONFIG['thumbcutmode'];
	$startx = $_SCONFIG['thumbcutstartx'];
	$starty = $_SCONFIG['thumbcutstarty'];
	$dstW = intval($thumbsizearr[0]);
	$dstH = intval($thumbsizearr[1]);
	if($dstW<20) $dstW = 100;
	if($dstH<20) $dstH = 100;

	$imgtype = array(1=>'gif', 2=>'jpeg', 3=>'png');

	$func_output = 'ImageJpeg';
	if (!function_exists ($func_output)) {
	    return '';
	}

	$data = @getimagesize($srcfile_file);
	if(!empty($data) && is_array($data) && $data[2] != 1 && $data['mime'] != 'image/gif') {
	} else {
		return '';
	}

	$func_create = "imagecreatefrom".$imgtype[$data[2]];
	if (!function_exists ($func_create)) {
	    return '';
	}

	$im = @$func_create($srcfile_file);
	$srcW = @imagesx($im);
	$srcH = @imagesy($im);
	$srcX = 0;
	$srcY = 0;
	$dstX = 0;
	$dstY = 0;

	//SIZE
	if($srcW < $dstW) $dstW = $srcW;
	if($srcH < $dstH) $dstH = $srcH;

	if ($option & $opbestresizew) {
	    $dstH = round($dstW * $srcH / $srcW);
	}
	if ($option & $opbestresizeh) {
	    $dstW = round($dstH * $srcW / $srcH);
	}

	$fdstW = $dstW;
	$fdstH = $dstH;

	//CUT
	if ($cutmode != 0) {
	    $srcW -= $startx;
	    $srcH -= $starty;
	    if ($srcW*$dstH > $srcH*$dstW) {
	        $testW = round($dstW * $srcH / $dstH);
	        $testH = $srcH;
	    } else {
	        $testH = round($dstH * $srcW / $dstW);
	        $testW = $srcW;
	    }
	    switch ($cutmode) {
	        case 1: $srcX = 0; $srcY = 0;
	        		break;
	        case 2: $srcX = round(($srcW - $testW) / 2);
					$srcY = round(($srcH - $testH) / 2);
					break;
	        case 3: $srcX = $srcW - $testW;
	                $srcY = $srcH - $testH;
	                break;
	    }
	    $srcW = $testW;
	    $srcH = $testH;
	    $srcX += $startx;
	    $srcY += $starty;
	} else {
	    if (!($option & $opnotkeepscale)) {
	         if ($srcW*$dstH > $srcH*$dstW) {
	            $fdstH = round($srcH*$dstW/$srcW);
	            $dstY = floor(($dstH-$fdstH)/2);
	            $fdstW = $dstW;
	        } else {
	            $fdstW = round($srcW*$dstH/$srcH);
	            $dstX = floor(($dstW-$fdstW)/2);
	            $fdstH = $dstH;
	        }
	        $dstX=($dstX<0)?0:$dstX;
	        $dstY=($dstX<0)?0:$dstY;
	        $dstX=($dstX>($dstW/2))?floor($dstW/2):$dstX;
	        $dstY=($dstY>($dstH/2))?floor($dstH/s):$dstY;
	    }
	}

	if(function_exists("imagecopyresampled") and function_exists("imagecreatetruecolor")) {
	    $func_create = "imagecreatetruecolor";
	    $func_resize = "imagecopyresampled";
	} elseif (function_exists("imagecreate") and function_exists("imagecopyresized")) {
	    $func_create = "imagecreate";
	    $func_resize = "imagecopyresized";
	} else {
		return '';
	}

	$newim = @$func_create($dstW,$dstH);
	$black = @imagecolorallocate($newim, 0,0,0);
	$back = @imagecolortransparent($newim, $black);
	@imagefilledrectangle($newim,0,0,$dstW,$dstH,$black);
	@$func_resize($newim,$im,$dstX,$dstY,$srcX,$srcY,$fdstW,$fdstH,$srcW,$srcH);
	@$func_output($newim, $dstfile_file);
	@imagedestroy($im);
	@imagedestroy($newim);
	
	if(!file_exists($dstfile_file)) {
		return '';
	}

	return $dstfile;
}

function makewatermark($srcfile) {
	global $_SCONFIG;

	if($_SCONFIG['watermark'] && function_exists('imageCreateFromJPEG') && function_exists('imageCreateFromPNG') && function_exists('imageCopyMerge')) {
		
		$srcfile = A_DIR.'/'.$srcfile;

		$watermark_file = $_SCONFIG['watermarkfile'];
		$watermarkstatus = $_SCONFIG['watermarkstatus'];

		$attachinfo	= @getimagesize($srcfile);
		if(!empty($attachinfo) && is_array($attachinfo) && $attachinfo[2] != 1 && $attachinfo['mime'] != 'image/gif') {
		} else {
			return '';
		}

		if(!$watermark_logo = @imageCreateFromGIF($watermark_file)) return '';
		$logo_w		= imageSX($watermark_logo);
		$logo_h		= imageSY($watermark_logo);
		$img_w		= $attachinfo[0];
		$img_h		= $attachinfo[1];
		$wmwidth	= $img_w - $logo_w;
		$wmheight	= $img_h - $logo_h;

		if(is_readable($watermark_file) && $wmwidth > 100 && $wmheight > 100) {
			switch ($attachinfo['mime']) {
				case 'image/jpeg':
					$dst_photo = imageCreateFromJPEG($srcfile);
					break;
				case 'image/gif':
					$dst_photo = imageCreateFromGIF($srcfile);
					break;
				case 'image/png':
					$dst_photo = imageCreateFromPNG($srcfile);
					break;
				default:
					break;
			}

			switch($watermarkstatus) {
				case 1:
					$x = +5;
					$y = +5;
					break;
				case 2:
					$x = ($logo_w +	$img_w)	/ 2;
					$y = +5;
					break;
				case 3:
					$x = $img_w - $logo_w-5;
					$y = +5;
					break;
				case 4:
					$x = +5;
					$y = ($logo_h +	$img_h)	/ 2;
					break;
				case 5:
					$x = ($logo_w +	$img_w)	/ 2;
					$y = ($logo_h +	$img_h)	/ 2;
					break;
				case 6:
					$x = $img_w - $logo_w;
					$y = ($logo_h +	$img_h)	/ 2;
					break;
				case 7:
					$x = +5;
					$y = $img_h - $logo_h-5;
					break;
				case 8:
					$x = ($logo_w +	$img_w)	/ 2;
					$y = $img_h - $logo_h;
					break;
				case 9:
					$x = $img_w - $logo_w-5;
					$y = $img_h - $logo_h-5;
					break;
			}

			imageAlphaBlending($watermark_logo, true);
			imageCopyMerge($dst_photo, $watermark_logo, $x,	$y, 0, 0, $logo_w, $logo_h, $_SCONFIG['watermarktrans']);

			switch($attachinfo['mime']) {
				case 'image/jpeg':
					imageJPEG($dst_photo, $srcfile, $_SCONFIG['watermarkjpgquality']);
					break;
				case 'image/gif':
					imageGIF($dst_photo, $srcfile);
					break;
				case 'image/png':
					imagePNG($dst_photo, $srcfile);
					break;
			}
		}
	}
}


?>