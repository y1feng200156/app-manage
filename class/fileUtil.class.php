<?php
$ROOT = $_SERVER ['DOCUMENT_ROOT'] .
		 substr ( $_SERVER ['PHP_SELF'], 0, 
				strpos ( $_SERVER ['PHP_SELF'], '/', 2 ) + 1 );
require_once $ROOT.'class/FOPClass.php';
include_once $ROOT . 'class/fileTypeCheck.php';
/**
 * 文件操作类
 * @author 谭超
 *
 */
class FileUtilSave {
	public static $ext_arr = array (
					'image' => array ('gif', 'jpg', 'jpeg', 'png', 'bmp' ), 
					'application' => array ('gif', 'jpg', 'jpeg', 'png', 'bmp','rmvb' ), 
					'flash' => array ('swf', 'flv' ), 
					'media' => array (
							'swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 
							'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb' ), 
					'file' => array (
							'doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 
							'html', 'txt', 'zip', 'rar', 'gz', 'bz2' ),
					'video' => array (
							'swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 
							'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb',
							"mov","wmv","asf","asx","mpeg","avi","mpg",
							"vob","mp4","3gp" ) );
	
	/**
	 * 
	 * 保存文件，利用递归，找出最终的文件进行保存
	 * @param unknown_type $file file对象
	 * @param unknown_type $save_path 保存的服务器根目录
	 * @param array $fileType 需要校验的文件类型
	 * @param unknown_type $type  是否要转换成json格式
	 * @param unknown_type $is_override  是否已覆盖形式保存文件
	 * @param $max_size 上传文件大小限制
	 * @param $isCheckType 是否检测文件类型，默认true
	
	 */
	static function saveFile($file,$save_path,array $fileType,$type=true,$is_override=false,$max_size=10485760,$isCheckType=true) {
		global $_SGLOBAL;
		$fileSavePaths=array();
		if (!empty ( $file )) {
		$fileArray=array();
		$file_name = $file ['name'] ;
			if (is_array($file_name)){
				$fileArray=self::fixGlobalFilesArray($file);
				foreach ($fileArray as $subkey=>$value) {
					if (!empty($value['name'])) {
						$fileSavePaths[$subkey]=self::saveFile($value, $save_path,$fileType,$type,$is_override,$max_size);
					}
				}
			}else{
				$file_name = $file ['name'];
				//服务器上临时文件名
				$tmp_name = $file  ['tmp_name'];
				//文件大小
				$file_size = $file ['size'];
				//文件类型
				$file_type = $file['type'];
				//检查文件名
				if (! $file_name) {
					if(!$type){
						$result = array("error"=>"1","message"=>urldecode(urlencode( "请选择文件。" )));
						echo json_encode($result);
						exit;
					}else{
						$result = array("error"=>"1","message"=>urldecode(urlencode( "请选择文件。" )));
						echo json_encode($result);
						exit;
					}
				}
				//检查是否已上传
				if (@is_uploaded_file ( $tmp_name ) === false) {
					if(!$type){
						$result = array("error"=>"1","message"=>urldecode(urlencode( "上传失败。" )));
						echo json_encode($result);
						exit;
					}else{
						$result = array("error"=>"1","message"=>urldecode(urlencode( "上传失败。" )));
						echo json_encode($result);
						exit;
					}
				}
				//检查文件大小
				if ($file_size >$max_size) {
					if(!$type){
						$result = array("error"=>"1","message"=>urldecode(urlencode( "上传文件大小超过限制。" )));
						echo json_encode($result);
						exit;
					}else{
						$result = array("error"=>"1","message"=>urldecode(urlencode( "上传文件大小超过限制。" )));
						echo json_encode($result);
						exit;
					}
				}
				//获得文件扩展名
				$temp_arr = explode ( ".", $file_name );
				$temp_type = explode ( "/", $file_type );
				$file_ext = array_pop ( $temp_arr );
				$file_type=array_shift($temp_type);
				$file_ext = trim ( $file_ext );
				$file_type = trim ($file_type );
				$file_ext = strtolower ( $file_ext );
				$file_type = strtolower ($file_type );
				//检查扩展名
				if(count($fileType)){
				    $inarray=in_array ( $file_ext, $fileType)==false;
				}else{
				    $inarray==false;
				}
				if ($isCheckType&&($inarray||!FileTypeValidation::validation($tmp_name, $file_ext))) {
					if(!$type){
						echo json_encode(array("error"=>"1","message"=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode ( ",", self::$ext_arr [$file_type])));
						exit;
					}else{
						echo json_encode (array("error"=>"1","message"=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode ( ",", self::$ext_arr [$file_type] ) . "格式。"));
						exit;
					}
				}
				if (!$is_override) {
					//创建文件夹
//					$save_path .= $file_type . "/";
					$ym = date ( "Ym" );
					$save_path .= $ym . "/";
					//新文件名
					$new_file_name = $_SGLOBAL['idkey']."_".date ( "YmdHis" ) . '.' . $file_ext;
					//移动文件
					$file_path = $save_path . $new_file_name;
				}else{
					$file_path = $save_path;
				}
				//创建index文件
				self::createIndex($file_path);
				if (FileUtil::moveFile( $tmp_name, $file_path ) === false) {
					if(!$type){
						echo json_encode (array("error"=>"1","message"=>urldecode(urlencode( "上传文件失败。" .$file_path.'<br>'.$tmp_name ))));
						exit;
						echo urldecode(urlencode( "上传文件失败。" .$file_path.'<br>'.$tmp_name ));
					}else{
						echo json_encode (array("error"=>"1","message"=>"上传文件失败。" .$file_path.'<br>'.$tmp_name));
						exit;
//						echo( "上传文件失败。" .$file_path.'<br>'.$tmp_name);
					}
				}
				return $file_path;
			}
		}
		return $fileSavePaths;
	}

	public static function createIndex($file_path){
		//构造index.html文件目录
		$filearr = explode("/", $file_path);
		$filearr[(count($filearr)-1)] = "index.html";
		$index_path = implode("/", $filearr);
		if(!file_exists($index_path)){//判断不存在
			FileUtil::createFile($index_path);  
		}
	}
	
	/**
	 * 处理多文件上传，封住为一个文件key对应各个文件属性
	 * 
	 * @param unknown_type $files	文件对象
	 */
	public static function fixGlobalFilesArray($files) {
		$ret = array ();
		if (isset ( $files ['tmp_name'] )) {
			if (is_array ( $files ['tmp_name'] )) {
				foreach ( $files ['name'] as $idx => $name ) {
					$ret [$idx] = array (
							'name' => $name, 
							'tmp_name' => $files ['tmp_name'] [$idx], 
							'size' => $files ['size'] [$idx], 
							'type' => $files ['type'] [$idx], 
							'error' => $files ['error'] [$idx] );
				}
			} else {
				$ret = $files;
			}
		} else {
			foreach ( $files as $key => $value ) {
				$ret [$key] = self::fixGlobalFilesArray ( $value );
			}
		}
		return $ret;
	}
	
	public static function is_file_type($file_name,$file_type){
		//获得文件扩展名
		$temp_arr = explode ( ".", $file_name );
		$temp_type = explode ( "/", $file_type );
		$file_ext = array_pop ( $temp_arr );
		$file_type=array_shift($temp_type);
		$file_ext = trim ( $file_ext );
		$file_type = trim ($file_type );
		$file_ext = strtolower ( $file_ext );
		$file_type = strtolower ($file_type );
		//检查扩展名
		if (in_array ( $file_ext, self::$ext_arr [$file_type] ) === false) {
			return false ; 
		}
		return true;
	}
}
