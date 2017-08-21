<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 2017/8/22
 * Time: 上午12:36
 */
//设置时区
date_default_timezone_set('Asia/Shanghai');

/**
 *获取某一天的上周一的日期
 */
function getLastMonday($datestr='') {
	if ( empty($datestr) ) {
		$date_time = time();
	}else{
		$date_time = strtotime($datestr);
	}
	//获取$date_time为星期{N}, [1=>monday ..... 7=>sunday]
	$week = date('N', $date_time);

	if ( $week == 1 ) {
		$new_date = date('Y-m-d', strtotime('-1 monday', $date_time));
	}else{
		$new_date = date('Y-m-d', strtotime('-2 monday', $date_time));
	}

	return $new_date;
}