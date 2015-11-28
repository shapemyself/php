<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 15/10/15
 * Time: 下午10:00
 */
require_once '../lib/Utils.php';

class FileDiff{


    /**
     * @var 左比较文件的文件名
     */
    protected $leftFile;
    /**
     * @var 右比较文件的文件名
     */
    protected $rightFile;
    /**
     * @var 比较结果文件名
     */
    protected $resultFile;
    /**
     * @var 比较的结果信息
     */
    public $diffResult;
    /**
     * @var 编码格式
     */
    protected $encode;
    /**
     * @var 不同处的统计值
     */
    protected $diffCount;

    /**
     * @var 结果输入文件的句柄
     */
    protected $resultFileHandle;

    /**
     * @var 左比较文件的总行数
     */
    protected $leftFileSumLines;
    /**
     * @var 右比较文件的总行数
     */
    protected $rightFileSumLines;




    /**
     * @bref:比较两个文件的内容
     */
    protected function diff(){

    }

    /**
     * @bref:将两个文件比较的差异结果写入文件中
     */
    protected function readDiffResultToFile(){

    }

    /**
     * @bref:关闭所有已经打开的文件句柄
     */
    public function closeFileHandle (){

    }


}
