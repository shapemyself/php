<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 15/10/15
 * Time: 下午10:20
 */
require_once '../lib/Utils.php';
require_once '../lib/FileDiff.php';

const LEFT = 1;
const RIGHT = 2;


class JsonFileDiff extends FileDiff{



    /**
     * @var 处理节点跟踪数组
     */
//    private $traceIndexArray;

    public static $count = 0;

    /**
     * @var 两个文件的不同行数
     */
    protected $diffLines;
    /**
     * @var 左处理文件句柄
     */
    protected $leftFileHandle;
    /**
     * @var 右处理文件句柄
     */
    protected $rightFileHandle;

    /**
     * @bref:构造函数
     * @param $inputCmdOption
     *
     */
    public function __construct( $inputCmdOption ){
        $this->leftFile     = $inputCmdOption['l'];
        $this->rightFile    = $inputCmdOption['r'];
        $this->resultFile   = $inputCmdOption['o'];
        if ( isset($inputCmdOption['e']) ){
            $this->encode  = $inputCmdOption['e'];
        }else{
            $this->encode  = 'utf8';
        }
        //初始化一些成员变量
        $this->leftFileSumLines     = 0;
        $this->rightFileSumLines    = 0;
        $this->diffLines    = 0;
        $this->diffCount = 0;
        //比较的结果存放在数组中
        $this->diffResult   = array();

        $this->leftFileHandle   = fopen($this->leftFile, 'r');
        $this->rightFileHandle  = fopen($this->rightFile, 'r');

        //节点跟踪数组初始化
//        $this->traceIndexArray = array();
    }


    /**
     * @bref: 每次从文件句柄中读取一行
     * @param $fileHandle
     * @param $lineCount
     * @return bool|string
     */
    private function readLine(&$fileHandle, &$lineCount){
        if ( feof($fileHandle) ){
            return false;
        }
        $line = fgets( $fileHandle );
        $lineCount++;
        return $line;
    }

    /**
     * @bref:边一行一行地读取两个json字符串文件中数据边进行比较
     *
     */
    public function diff(){
        //循环从左比较文件中读取一行数据
        while ( ($leftLine = $this->readLine($this->leftFileHandle,$this->leftFileSumLines)) !== false ){
            //如果文件的编码格式不是utf8则转换成utf8
            if ( $this->encode != 'utf8' ){
                $leftLine = mb_convert_encoding($leftLine, 'utf8', $this->encode);
            }
            //从右比较文件中读取一行数据
            $rightLine = $this->readLine($this->rightFileHandle,$this->rightFileSumLines);
            if ( $rightLine === false ){
                //下面为比较结果的存储结构
                $this->diffResult[] = array(
                    'lineno'       =>$this->leftFileSumLines,
                    'nodeInfo'     =>'',
                    'left'         =>$leftLine,
                    'right'        =>'',
                );
                continue;
            }
            //如果文件的编码格式不是utf8则转换成utf8
            if ( $this->encode != 'utf8' ){
                $rightLine = mb_convert_encoding($rightLine, 'utf8', $this->encode);
            }
            //首先判断两者是否相等,如果相等,则说明没有差异,不再继续比较
            if ( $leftLine == $rightLine ){
                continue;
            }else{
                $this->diffLines++;
            }
            //将$leftLine和$rightLine的json字符串解析成数组
            $leftLineToArray = json_decode($leftLine, true);
            $rightLineToArray = json_decode($rightLine, true);

            //使用数组来索引跟踪比较的节点
            $traceIndexArray = array();
            $this->findDiff( $leftLineToArray, $rightLineToArray, $traceIndexArray );
        }
        while ( ( $rightLine = $this->readLine($this->rightFileHandle,$this->rightFileSumLines)) !== false){
            $this->diffLines++;//不同行数递增
            $this->diffResult[] = array(
                'lineno'       =>$this->rightFileSumLines,
                'nodeInfo'     =>'',
                'left'         =>'',
                'right'        =>$rightLine,
            );
        }
        //统计两个文件的总不同之处
        $this->diffCount = count($this->diffResult);
    }

    /**
     * @bref:广度遍历两个数组之间的差别以及深度遍历两个数组之间的区别
     * @param $leftLineToArray
     * @param $rightLineToArray
     * @param $traceIndexArray
     */
    private function findDiff( $leftLineToArray, $rightLineToArray, $traceIndexArray ){
        //如果两边中至少有一个是非数组
        if (  !is_array( $leftLineToArray ) || !is_array( $rightLineToArray )  ){
            //将参数为数组的参数转换成json字符串
            if ( is_array( $leftLineToArray ) ){
                $leftLineToArray = json_encode( $leftLineToArray );
            }
            if ( is_array( $rightLineToArray ) ){
                $rightLineToArray = json_encode( $rightLineToArray );
            }
            $this->diffResult[] = array(
                'lineno'        =>$this->rightFileSumLines,
                'nodeInfo'      =>$traceIndexArray,
                'left'          =>$leftLineToArray,
                'right'         =>$rightLineToArray,
            );
            //退出
            return;
        }
        //如果两边都是数组
        foreach( $leftLineToArray as $key => $value ){
            $tmpTraceArray = $traceIndexArray;
            $tmpTraceArray[] = $key;
            //如果$rightLineToArray中不存在key为$key
            if ( !array_key_exists( $key, $rightLineToArray ) ){
                //如果$leftLineToArray[$key]为数组
                if ( is_array( $leftLineToArray[$key] ) ){
                    $leftDiff = json_encode( $leftLineToArray[$key] );
                }else{
                    $leftDiff = $leftLineToArray[$key];
                }
                $this->diffResult[] = array(
                    'lineno'        =>$this->rightFileSumLines,
                    'nodeInfo'      =>$tmpTraceArray,
                    'left'          =>$leftDiff,
                    'right'         =>'',
                );
                continue;
            }
            //如果$rightLineToArray[$key]不为空

            //如果两者相等
            if ( $leftLineToArray[$key] == $rightLineToArray[$key] ){
                continue;
            }
            //如果两者存在且不相等,递归对比
            $this->findDiff( $leftLineToArray[$key], $rightLineToArray[$key], $tmpTraceArray );
        }

        //将isset($leftLineToArray[$key1])==false && isset($rightLineToArray[$key2])==true时的diff写入diff数组
        foreach( $rightLineToArray as $key => $value ){
            $tmpTraceArray = $traceIndexArray;
            $tmpTraceArray[] = $key;
            if ( !array_key_exists( $key, $leftLineToArray ) ){
                if ( is_array( $rightLineToArray[$key] ) ){
                    $rightDiff = json_encode( $rightLineToArray[$key] );
                }else{
                    $rightDiff = $rightLineToArray[$key];
                }
                $this->diffResult[] = array(
                    'lineno'        =>$this->rightFileSumLines,
                    'nodeInfo'      =>$tmpTraceArray,
                    'left'          =>'',
                    'right'         =>$rightDiff,
                );
            }
        }

    }


    /**
     * @bref:将两个json字符串中的diff写入到结果文件中
     */
    public function readDiffResultToFile() {
        //获取结果写入文件的句柄

        $this->resultFileHandle = fopen($this->resultFile, 'w');

        //向文件中输入每行的信息
        $lineInfo = '输出如下的结果:' . Utils::LINE_FEED;
        $lineInfo .= 'left line no: ' . $this->leftFileSumLines.', right line no:' . $this->rightFileSumLines . Utils::LINE_FEED;
        $lineInfo .= 'there are ' . $this->diffCount . 'diff[s] in ' . $this->diffLines .
                     'line[s], next is the diff detail differences:'.Utils::LINE_FEED;
        $lineInfo .= '+++++++++++++++++' . Utils::LINE_FEED;
        //向结果文件中写入比较差异结果的头部信息
        fwrite($this->resultFileHandle, $lineInfo);
        if ( count($this->diffResult)>0 ){
            foreach($this->diffResult as $key => $value){
                $lineInfo = '---line' . $value['lineno'] . ':';
                if ( is_array($value['nodeInfo']) && count($value['nodeInfo'])>0 ){
                    foreach ($value['nodeInfo'] as $key1 => $value1){
                        if ( is_numeric($value1) ){
                            $lineInfo .= '[' . $value1 . ']->';
                        }else{
                            $lineInfo .= $value1 . '->';
                        }
                    }
                    //去掉最后的->
                    $lineInfo = substr($lineInfo, 0, strlen($lineInfo)-2);
                }
                //postion start 0 not 1,当第一个字符为[或者{时,两边不加""
                if ( strpos( $value['left'], '{' ) === 0 || strpos( $value['left'], '[') === 0 ){
                    $lineInfo .= Utils::LINE_FEED . Utils::SEPARATOR_FEED . Utils::SEPARATOR_FEED . ">" . $value['left'] .
                        Utils::LINE_FEED;
                }else{
                    $lineInfo .= Utils::LINE_FEED . Utils::SEPARATOR_FEED . Utils::SEPARATOR_FEED . ">" . Utils::DOUBLE_QUOTTE . $value['left'] . Utils::DOUBLE_QUOTTE . Utils::LINE_FEED;
                }
                if ( strpos( $value['right'], '{') === 0 || strpos( $value['right'], '[') === 0 ){
                    $lineInfo .= Utils::SEPARATOR_FEED . Utils::SEPARATOR_FEED . "<" . $value['right'] . Utils::LINE_FEED;
                }else{
                    $lineInfo .= Utils::SEPARATOR_FEED . Utils::SEPARATOR_FEED  . "<" . Utils::DOUBLE_QUOTTE .
                        $value['right'] . Utils::DOUBLE_QUOTTE . Utils::LINE_FEED;
                }
                fwrite($this->resultFileHandle, $lineInfo);
                unset($lineInfo);
            }
        }
    }

    /**
     * @bref:关闭两个比较文件以及结果存放文件的句柄
     *
     */
    public function closeFileHandle (){
        //关闭左比较文件的句柄
        fclose($this->leftFileHandle);
        //关闭右比较文件的句柄
        fclose($this->rightFileHandle);
        //关闭输出结果文件的句柄
        fclose($this->resultFileHandle);
    }

}














