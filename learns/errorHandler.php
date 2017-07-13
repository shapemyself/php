<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 2017/4/30
 * Time: 上午10:42
 */
function dealError($errorNo, $errStr, $errFile, $errLine) {
    echo sprintf('error: no=%s, info=%s, location=%s, line=%s',
        $errorNo,
        $errStr,
        $errFile,
        $errLine),PHP_EOL;
}


//set_error_handler('dealError');
//$arr = [];
//echo $arr[good];

function testErrorTrigger() {
    $division = 0;
    if ( $division ==0 ) {
        trigger_error('the division is zero, trigger error');
    }
}

//testErrorTrigger();

function testFileContent() {
    $html = file_get_contents('http://www.baidu.com/');
    print_r($http_response_header);
    $fp = fopen('http://www.baidu.com/', 'r');
    $result = stream_get_meta_data($fp);
    print_r($result);
}



testFileContent();














