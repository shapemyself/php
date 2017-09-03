<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 2017/9/3
 * Time: 下午12:32
 */


/**
 * @bref:回调方法
 * $_GET['model'] = 'Model_xxx';
 * $_GET['func'] = 'funcname',
 * $_GET['params'] = json_encode([$paramValue_1,$paramValue_2,...]);
 */
function callbackNew() {
	if ( !isset($_GET['func']) || !isset($_GET['model']) ) {
		$this->outJsonp(-1, 'error param');
	}

	$model = $_GET['model'];
	$func = $_GET['func'];
	$param = $_GET['params'];
	if ( !class_exists($model) ) {
		$this->outJsonp(-1, 'error param');
	}
	$modelObj = new $model();
	if ( !method_exists($modelObj, $func) ) {
		$this->outJsonp(-1, 'error param');
	}
	$params = empty($param) ? array() : json_decode($param, true);

	$method = new ReflectionMethod($modelObj, $func);
	//判断是否为共有方法
	if ( !$method->isPublic() ) {
		$this->outJsonp(-1, 'error param');
	}

	//判断传入的函数参数个数是否正确
	if ( empty($params) || $method->getNumberOfRequiredParameters() > count($params) ) {
		$this->outJsonp(-1, 'error param');
	}

	$res = call_user_func_array(array($modelObj, $func), $params);
	$this->outJsonp(0, 'ok', $res);
}