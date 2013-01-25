<?php
$ROOT = $_SERVER ['DOCUMENT_ROOT'] .
		 substr ( $_SERVER ['PHP_SELF'], 0, 
				strpos ( $_SERVER ['PHP_SELF'], '/', 2 ) + 1 );
require_once $ROOT.'class/FOPClass.php';
include_once $ROOT . 'class/fileTypeCheck.php';
/**
 * �ļ�������
 * @author ̷��
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
	 * �����ļ������õݹ飬�ҳ����յ��ļ����б���
	 * @param unknown_type $file file����
	 * @param unknown_type $save_path ����ķ�������Ŀ¼
	 * @param array $fileType ��ҪУ����ļ�����
	 * @param unknown_type $type  �Ƿ�Ҫת����json��ʽ
	 * @param unknown_type $is_override  �Ƿ��Ѹ�����ʽ�����ļ�
	 * @param $max_size �ϴ��ļ���С����
	 * @param $isCheckType �Ƿ����ļ����ͣ�Ĭ��true
	
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
				//����������ʱ�ļ���
				$tmp_name = $file  ['tmp_name'];
				//�ļ���С
				$file_size = $file ['size'];
				//�ļ�����
				$file_type = $file['type'];
				//����ļ���
				if (! $file_name) {
					if(!$type){
						$result = array("error"=>"1","message"=>urldecode(urlencode( "��ѡ���ļ���" )));
						echo json_encode($result);
						exit;
					}else{
						$result = array("error"=>"1","message"=>urldecode(urlencode( "��ѡ���ļ���" )));
						echo json_encode($result);
						exit;
					}
				}
				//����Ƿ����ϴ�
				if (@is_uploaded_file ( $tmp_name ) === false) {
					if(!$type){
						$result = array("error"=>"1","message"=>urldecode(urlencode( "�ϴ�ʧ�ܡ�" )));
						echo json_encode($result);
						exit;
					}else{
						$result = array("error"=>"1","message"=>urldecode(urlencode( "�ϴ�ʧ�ܡ�" )));
						echo json_encode($result);
						exit;
					}
				}
				//����ļ���С
				if ($file_size >$max_size) {
					if(!$type){
						$result = array("error"=>"1","message"=>urldecode(urlencode( "�ϴ��ļ���С�������ơ�" )));
						echo json_encode($result);
						exit;
					}else{
						$result = array("error"=>"1","message"=>urldecode(urlencode( "�ϴ��ļ���С�������ơ�" )));
						echo json_encode($result);
						exit;
					}
				}
				//����ļ���չ��
				$temp_arr = explode ( ".", $file_name );
				$temp_type = explode ( "/", $file_type );
				$file_ext = array_pop ( $temp_arr );
				$file_type=array_shift($temp_type);
				$file_ext = trim ( $file_ext );
				$file_type = trim ($file_type );
				$file_ext = strtolower ( $file_ext );
				$file_type = strtolower ($file_type );
				//�����չ��
				if(count($fileType)){
				    $inarray=in_array ( $file_ext, $fileType)==false;
				}else{
				    $inarray==false;
				}
				if ($isCheckType&&($inarray||!FileTypeValidation::validation($tmp_name, $file_ext))) {
					if(!$type){
						echo json_encode(array("error"=>"1","message"=>"�ϴ��ļ���չ���ǲ��������չ����\nֻ����" . implode ( ",", self::$ext_arr [$file_type])));
						exit;
					}else{
						echo json_encode (array("error"=>"1","message"=>"�ϴ��ļ���չ���ǲ��������չ����\nֻ����" . implode ( ",", self::$ext_arr [$file_type] ) . "��ʽ��"));
						exit;
					}
				}
				if (!$is_override) {
					//�����ļ���
//					$save_path .= $file_type . "/";
					$ym = date ( "Ym" );
					$save_path .= $ym . "/";
					//���ļ���
					$new_file_name = $_SGLOBAL['idkey']."_".date ( "YmdHis" ) . '.' . $file_ext;
					//�ƶ��ļ�
					$file_path = $save_path . $new_file_name;
				}else{
					$file_path = $save_path;
				}
				//����index�ļ�
				self::createIndex($file_path);
				if (FileUtil::moveFile( $tmp_name, $file_path ) === false) {
					if(!$type){
						echo json_encode (array("error"=>"1","message"=>urldecode(urlencode( "�ϴ��ļ�ʧ�ܡ�" .$file_path.'<br>'.$tmp_name ))));
						exit;
						echo urldecode(urlencode( "�ϴ��ļ�ʧ�ܡ�" .$file_path.'<br>'.$tmp_name ));
					}else{
						echo json_encode (array("error"=>"1","message"=>"�ϴ��ļ�ʧ�ܡ�" .$file_path.'<br>'.$tmp_name));
						exit;
//						echo( "�ϴ��ļ�ʧ�ܡ�" .$file_path.'<br>'.$tmp_name);
					}
				}
				return $file_path;
			}
		}
		return $fileSavePaths;
	}

	public static function createIndex($file_path){
		//����index.html�ļ�Ŀ¼
		$filearr = explode("/", $file_path);
		$filearr[(count($filearr)-1)] = "index.html";
		$index_path = implode("/", $filearr);
		if(!file_exists($index_path)){//�жϲ�����
			FileUtil::createFile($index_path);  
		}
	}
	
	/**
	 * ������ļ��ϴ�����סΪһ���ļ�key��Ӧ�����ļ�����
	 * 
	 * @param unknown_type $files	�ļ�����
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
		//����ļ���չ��
		$temp_arr = explode ( ".", $file_name );
		$temp_type = explode ( "/", $file_type );
		$file_ext = array_pop ( $temp_arr );
		$file_type=array_shift($temp_type);
		$file_ext = trim ( $file_ext );
		$file_type = trim ($file_type );
		$file_ext = strtolower ( $file_ext );
		$file_type = strtolower ($file_type );
		//�����չ��
		if (in_array ( $file_ext, self::$ext_arr [$file_type] ) === false) {
			return false ; 
		}
		return true;
	}
}
