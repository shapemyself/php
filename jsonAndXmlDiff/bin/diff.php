<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 15/10/15
 * Time: 下午5:29
 */

require_once '../lib/Utils.php';
require_once '../lib/JsonFileDiff.php';
require_once '../lib/XmlFileDiff.php';

/**
 * t:(必须有参数,json/xml)
 * l:(必须有参数,左比较文件)
 * f:(必须有参数,右比较文件)
 * o:(必须有参数,输出文件)
 * h(帮助,没有参数)
 * v(版本,没有参数)
 */
$options = getopt('t:l:r:o:e:hv');


//设置日志文件
Utils::setLogFileName();

if ( array_key_exists('v', $options) ){
    Utils::getVersion();
    return 0;
}

if ( array_key_exists('h', $options) ){
    Utils::showHelp();
    return 0;
}

if ( !Utils::checkInputCmd($options) ){
    echo Utils::CMD_ERR_PROMPT;
    Utils::showHelp();
    return -1;
}

//判断输入命令中的文件的有效性
if ( !Utils::isFilesExist($options) ){
    echo Utils::CMD_FILE_NOT_EXIT_ERROR;
    Utils::showHelp();
    return -1;
}

if ( strcasecmp($options['t'], 'json') === 0 ){
    $diffObject = new JsonFileDiff($options);
}else{
    $diffObject = new XmlFileDiff($options);
}
//开始比较文件的差异
$diffObject->diff();

//将结果写入文件中
$diffObject->readDiffResultToFile();

//关闭文件句柄
$diffObject->closeFileHandle();

















