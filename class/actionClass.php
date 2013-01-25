<?php

class ActionClass {
	private $resultArray = array ();
    
    function __construct($classObj) {
        $this->actionRequest($classObj);
    }
	/**
	 * 动态调用方法，并且赋值变量。如果不指定方法名，默认调用index方法，如没找不到，
	 * 出现“Call to undefined method”错误
	 * @param class $classObj	类实例
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
	 * 动态赋值变量
	 * @param unknown_type $value	变量值
	 * @param unknown_type $key		变量名
	 * @param unknown_type $c		类实例
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
	 *根据filter来组合条件查询字符串
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
	 * 循环获取的request值，并且重组value值
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
	 * 获取当前类名，如果继承了该类，则为子类名字
	 */
	public function getClassName() {
		return get_class ( $this );
	}
}
?>