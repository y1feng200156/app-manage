<?php

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

class upload {
	var $Port;
	var $Addr;
	var $Boundary;
	var $fileName;
	var $fileOffset;
	var $fileLength;
	var $fileInfo;
	var $tmpFile;
	var $saveFile;
	var $fileext;
	
	function __construct() {
		global $_SGLOBAL, $socket_port, $socket_server;
		
		if(empty($_SGLOBAL['supe_uid'])) {
			echo '<script>alert("ERROR!NO LOGIN");</script>';
			exit();
		}

		$this->Port = empty($socket_port)?mt_rand(1024, 65536):$socket_port;
		$this->Addr = empty($socket_server)?$_SERVER['SERVER_NAME']:$socket_server;
		$this->fileInfo = S_ROOT.'./video/temp/'.$_SGLOBAL['supe_uid'].'.dat';
		$this->tmpFile = S_ROOT.'./video/temp/'.$_SGLOBAL['supe_uid'].'.tmp';
	}
	
    function upload() {
    	$this->__construct();
    }
    
    function getRequest() {
    	global $_SGLOBAL, $_SCONFIG, $space, $slang;

    	if(!function_exists('socket_create')) {
    		writefile($this->fileInfo, 'error|'.$slang['upload_failed'].'\nFatal error: Call to undefined function: socket_create()\nPlease check your php.ini extension php_sockets.dll', 'text', 'w', 0);
    		exit();
    	}
    	if(!@$Socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
    		writefile($this->fileInfo, 'error|'.$slang['upload_failed'].'\nFatal error: socket_create failed', 'text', 'w', 0);
    		exit();
    	}
    	if (socket_bind($Socket, $_SERVER['SERVER_ADDR'], $this->Port) < 0) {
    		writefile($this->fileInfo, 'error|'.$slang['upload_failed'].'\nFatal error: socket_bind failed', 'text', 'w', 0);
    		exit();
		}
		if (socket_listen($Socket, 5) < 0) {
			writefile($this->fileInfo, 'error|'.$slang['upload_failed'].'\nFatal error: socket_listen failed', 'text', 'w', 0);
    		exit();
		}

		writefile($this->fileInfo, $this->Addr.':'.$this->Port, 'text', 'w', 0);

		$Request = socket_accept($Socket);
		if ($Request < 0) {
			unlink($this->fileInfo);
			writefile($this->fileInfo, 'error|'.$slang['upload_failed'].'\nFatal error: socket_accept failed', 'text', 'w', 0);
    		exit();
		}
		$Bufferarr = array();
		$data = 0;
		$bufferall = '';
		
		$videotypes = explode(',', $_SCONFIG['videotypes']);

		$fp = fopen($this->tmpFile, 'wb');
		while ($Flag = socket_recv($Request, $buffer, 1024, 0)) {
 			if(empty($this->fileOffset)) {
				$bufferall .= $buffer;	//没有获取到offset
			} else {
				fwrite($fp, $buffer);	//已经获取
			}

			array_push($Bufferarr, $buffer);
			if (count($Bufferarr) == 3) {
				$data += strlen($Bufferarr[0]);
				array_shift($Bufferarr);
			}
			$Contents = join("", $Bufferarr);
			if (empty($this->Boundary)) {
				if (preg_match("/Content-Type: multipart\/form-data; boundary=[-]{27}(\S+)/i", $Contents, $matchesB)) {
					$this->Boundary = $matchesB[1];
				}
			}
			if (empty($this->fileLength)) {
				if (preg_match("/Content-Length: (\d+)\r\n/i", $Contents, $matchesC)) {
					$this->fileLength = $matchesC[1] - strlen($this->Boundary) - 37;
				}
			}
			$boundary = str_repeat('-', 29).$this->Boundary;
			if (strpos($Contents, $boundary)) {
				preg_match_all("/$boundary\r\nContent-Disposition: form-data; name=\"([^\"]*)\"(; filename=\"([^\"]*)\"\r\nContent-Type: (\S+))?\r\n/i", $Contents, $matchesF, PREG_OFFSET_CAPTURE);
				if (!empty($matchesF[1])) {
					$this->fileName = $this->getBaseName($matchesF[3][0][0]);
					$this->fileext = fileext($this->fileName);
					
					//检查文件类型
					//flv
					if($this->fileext != 'flv' && !in_array($this->fileext, $videotypes)) {
						fclose($fp);
						socket_close($Request);
						socket_close($Socket);
						$slang['upload_video_type_nonsupport'] = str_replace('{TYPE}', ($_SCONFIG[videotypes]), $slang['upload_video_type_nonsupport']);
						writefile($this->fileInfo, "error|$slang[upload_video_type_nonsupport] ", 'text', 'w', 0);
			    		exit();
					}
					
					if (empty($this->fileOffset)) {
						$this->fileLength -= strlen($matchesF[0][0][0]);
						$this->fileOffset = $data + $matchesF[0][0][1] + strlen($matchesF[0][0][0]);
						
						//判断大小
						if(empty($this->fileLength)) {
							fclose($fp);
							socket_close($Request);
							socket_close($Socket);
							writefile($this->fileInfo, "error|$slang[upload_failed]", 'text', 'w', 0);
				    		exit();
						}
						if(!empty($_SCONFIG['videomaxsize']) && $this->fileLength>$_SCONFIG['videomaxsize']*1024*1024) {
							fclose($fp);
							socket_close($Request);
							socket_close($Socket);
							$slang['upload_size_too_big'] = str_replace('{MAXSIZE}', $_SCONFIG['videomaxsize'], $slang['upload_size_too_big']);
							writefile($this->fileInfo, "error|$slang[upload_size_too_big]", 'text', 'w', 0);
				    		exit();
						}
						if(!empty($_SGLOBAL['group']['attachsize'])) {
							//检查用户剩余大小
							$spacesize = ($space['spacesize'] + $_SGLOBAL['group']['attachsize'])*1024*1024;
							if($space['attachsize'] + $this->fileLength > $spacesize) {
								fclose($fp);
								socket_close($Request);
								socket_close($Socket);
								writefile($this->fileInfo, "error|$slang[no_attachsize]", 'text', 'w', 0);
					    		exit();
							}
						}

						$filecontent = $this->Addr.':'.$this->Port.'|'.$this->fileext.'|'.$this->fileLength;
						writefile($this->fileInfo, $filecontent, 'text', 'w', 0);

						if(!empty($this->fileOffset) && !empty($bufferall)) {
							$bufferall = substr($bufferall, $this->fileOffset+2);	//第一次获取到offset
							if(!empty($bufferall)) fwrite($fp, $bufferall);
							unset($bufferall);
						}
					}
				} else {
					break;
				}
			}

			if ($Flag < 0) {
				break;
			} elseif ($Flag == 0) {
				break;
			}
			$eof = substr($buffer, -4);
			$las = substr($buffer, 1024 - 4, 4);
			if ($eof == '\x2d\x2d\x0d\x0a' || (strlen($eof) < 4 && ($las{strlen($eof) -1} == "\x0a" || $las{strlen($eof) -1} == "\x00"))) {
				break;
			}
		}
		fclose($fp);
		socket_close($Request);
		socket_close($Socket);
    }
    
    function getFileInfo() {
    	if(@$fp = fopen($this->fileInfo, 'rb')) {
	    	$content = explode('|',fread($fp, 1024));
	    	fclose($fp);
	    	return $content;
    	} else {
    		return '';
    	}
    }
    
    function delFileInfo() {
    	@unlink($this->fileInfo);
    	@unlink($this->tmpFile);
    }
    
    function getUploadSize() {
    	if (file_exists($this->tmpFile)) {
			return filesize($this->tmpFile);
		} else {
			return 0;
		}
    }
    
    function getBaseName($path) {
    	$path = str_replace("\\", "/", $path);
    	return substr($path, strrpos($path, "/") + 1);
    }
}
?>