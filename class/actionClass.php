<?php

class ActionClass {
	private $resultArray = array ();
    
    function __construct($classObj) {
        $this->actionRequest($classObj);
    }
	/**
	 * ��̬���÷��������Ҹ�ֵ�����������ָ����������Ĭ�ϵ���index��������û�Ҳ�����
	 * ���֡�Call to undefined method������
	 * @param class $classObj	��ʵ��
	 */
	public function actionRequest($classObj) {
		$request_mehod = $_GET;
		$actionname = substr ( $_REQUEST ['actionName'], 
				count ( $_REQUEST ['actionName'] ) - 5 );
		if (strcasecmp ( $actionname, 'post' ) == 0) {
			if ($_SERVER [REQUEST_METHOD] == 'POST')
				$request_mehod = $_POST;
			else
				throw new Exception ( "Does not meet the method." );
		} else if ($_SERVER [REQUEST_METHOD] == 'POST')
			throw new Exception ( "Does not meet the method." );
		array_walk ( $request_mehod, array ($this, "methodManger" ), 
				$classObj );
		array_walk ( $_FILES, array ($this, "methodManger" ), 
				$classObj );
		if (! method_exists ( $this, $request_mehod ['actionName'] )) {
			$m = 'index';
		} else {
			$m = empty ( $request_mehod ['actionName'] ) ? 'index' : $request_mehod ['actionName'];
		}
		return $classObj->$m ();
	}

	/**
	 * ��̬��ֵ����
	 * @param unknown_type $value	����ֵ
	 * @param unknown_type $key		������
	 * @param unknown_type $c		��ʵ��
	 */
	private function methodManger($value, $key, $c) {
		if ($key != 'actionName') {
			if (is_array ( $value )) {
				array_walk ( $value, array ($this, "methodManger" ), 
						$c );
			} 
			$m = 'set' . ucwords ( $key );
			if (method_exists ( $c, $m )) {
				$c->$m ( $value );
			}
		}
	}

	/**
	 *����filter�����������ѯ�ַ���
	 * @throws Exception
	 */
	public function propertyFilter() {
		$request_mehod = $_GET;
		$actionname = substr ( $_REQUEST [actionName],
				count ( $_REQUEST [actionName] ) - 5 );
		if (strcasecmp ( $actionname, 'post' ) == 0) {
			if ($_SERVER [REQUEST_METHOD] == 'POST')
				$request_mehod = $_POST;
			else
				throw new Exception ( "Does not meet the method." );
		} else if ($_SERVER [REQUEST_METHOD] == 'POST')
			throw new Exception ( "Does not meet the method." );
		array_walk ( $request_mehod,
				array ($this, "propertyFilterMethod" ) );
		$data = implode ( ' and', $this->resultArray );
		return ! empty ( $data ) ? ' where ' . $data : $data;
	}

	/**
	 * 
	 * ѭ����ȡ��requestֵ����������valueֵ
	 * @param unknown_type $value
	 * @param unknown_type $key
	 */
	private function propertyFilterMethod(&$value, $key) {
		preg_match ( "(filter_[A-Z]+_)", $key, $pregResult );
		$key = str_replace ( $pregResult [0], '', $key );
		$value = iconv ( "UTF-8", "GBK", $value );
		if ($pregResult [0] === "filter_LIKE_") {
			if (! empty ( $value )) {
				$this->resultArray [] = " $key like '%$value%'";
			}
		} else if ($pregResult [0] === "filter_LT_") {
			if (! empty ( $value )) {
				$this->resultArray [] = " $key < '$value'";
			}
		} else if ($pregResult [0] === "filter_LTD_") {
			if (! empty ( $value )) {
				$value = strtotime ( $value );
				$this->resultArray [] = " $key < '$value'";
			}
		} else if ($pregResult [0] === "filter_LE_") {
			if (! empty ( $value )) {
				$this->resultArray [] = " $key <= '$value'";
			}
		} else if ($pregResult [0] === "filter_LED_") {
			if (! empty ( $value )) {
				$value = strtotime ( $value );
				$this->resultArray [] = " $key <= '$value'";
			}
		} else if ($pregResult [0] === "filter_GT_") {
			if (! empty ( $value )) {
				$this->resultArray [] = " $key > '$value'";
			}
		} else if ($pregResult [0] === "filter_GTD_") {
			if (! empty ( $value )) {
				$value = strtotime ( $value );
				$this->resultArray [] = " $key > '$value'";
			}
		} else if ($pregResult [0] === "filter_GE_") {
			if (! empty ( $value )) {
				$this->resultArray [] = " $key >= '$value'";
			}
		} else if ($pregResult [0] === "filter_GED_") {
			if (! empty ( $value )) {
				$value = strtotime ( $value );
				$this->resultArray [] = " $key >= '$value'";
			}
		} else if ($pregResult [0] === "filter_EQ_") {
			if (! empty ( $value )) {
				$this->resultArray [] = " $key = '$value'";
			}
		}
	}

	/**
	 * ��ȡ��ǰ����������̳��˸��࣬��Ϊ��������
	 */
	public function getClassName() {
		return get_class ( $this );
	}
}
?>