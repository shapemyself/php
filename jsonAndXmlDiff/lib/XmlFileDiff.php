<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 15/10/15
 * Time: 下午10:20
 */
require_once '../lib/FileDiff.php';

class XmlFileDiff extends FileDiff{


    /**
     * @param $inputCmdOption
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
        $this->diffCount = 0;
        //比较的结果存放在数组中
        $this->diffResult   = array();
    }

    /**
     * @bref : 比较两个文件之间的区别
     */
    public function diff(){
        //将左右xml文件中的xml字符串转换成数组

        //左比较文件,读取文件中的xml字符串
        $leftFileInfoStr = file_get_contents( $this->leftFile );
        //由于解析后的数组会丢失第一个根节点的信息,在最外层加上一个节点
        $leftFileInfoStr = '<document>' . $leftFileInfoStr . '</document>';
        //如果文件的编码格式不是utf8,则将编码格式编程utf8
        if( $this->encode != 'utf8' ){
            $leftFileInfoStr = mb_convert_encoding($leftFileInfoStr, 'utf8', $this->encode);
        }
        $leftXmlFileObj = simplexml_load_string( $leftFileInfoStr );
        $leftJsonStr = json_encode( $leftXmlFileObj );
        $leftXmlFileToArray = json_decode( $leftJsonStr, true );

        //右比较文件,读取文件中的xml字符串
        $rightFileInfoStr = file_get_contents( $this->rightFile );
        //由于解析后的数组会丢失第一个根节点的信息,在最外层加上一个节点
        $rightFileInfoStr = '<document>' . $rightFileInfoStr . '</document>';
        //如果文件的编码格式不是utf8,则将编码格式编程utf8
        if( $this->encode != 'utf8' ){
            $rightFileInfoStr = mb_convert_encoding($rightFileInfoStr, 'utf8', $this->encode);
        }
        $rightXmlFileObj = simplexml_load_string( $rightFileInfoStr );
        $rightJsonStr = json_encode( $rightXmlFileObj );
        $rightXmlFileToArray = json_decode( $rightJsonStr, true );

        //跟踪节点的轨迹数组
        $trackNodeArray = array();
        if ( !count( $leftXmlFileToArray )>0 || !count( $rightXmlFileToArray )>0 ){
            $this->diffResult[] = array(
                'nodeInfo'      =>$trackNodeArray,
                'left'          =>file_get_contents( $this->leftFile ),
                'right'         =>file_get_contents( $this->rightFile ),
            );
        }else{
            $this->findDiff( $leftXmlFileToArray, $rightXmlFileToArray, $trackNodeArray );
        }

        //统计两个文件之间的差别
        $this->diffCount = count( $this->diffResult );
    }

    /**
     * @bref:从两个数组中查找出两者之间的区别
     * @param $leftXmlFileToArray
     * @param $rightXmlFileToArray
     * @param $trackNodeArray
     * @return
     */
    private function findDiff($leftXmlFileToArray, $rightXmlFileToArray, $trackNodeArray){
        //只要其中一个不是数组,则说明两者之间差别
        if ( !is_array( $leftXmlFileToArray ) || !is_array( $rightXmlFileToArray ) ){
            if ( is_array( $leftXmlFileToArray ) ){
                $leftDiff = $this->arrayToXmlStr( $leftXmlFileToArray );
            }else{
                $leftDiff = $leftXmlFileToArray;
            }
            if ( is_array( $rightXmlFileToArray ) ){
                $rightDiff = $this->arrayToXmlStr( $rightXmlFileToArray );
            }else{
                $rightDiff = $rightXmlFileToArray;
            }
            $this->diffResult[] = array(
                'nodeInfo'      =>$trackNodeArray,
                'left'          =>$leftDiff,
                'right'         =>$rightDiff,
            );
            return;
        }
        //如果两边都是数组
        foreach( $leftXmlFileToArray as $key => $value ){
            $tmpTraceArray = $trackNodeArray;
            $tmpTraceArray[] = $key;
            //如果$rightLineToArray中不存在key为$key
            if ( !array_key_exists( $key, $rightXmlFileToArray ) ){
                //如果$leftLineToArray[$key]为数组
                if ( is_array( $leftXmlFileToArray[$key] ) ){
                    $leftDiff = $this->arrayToXmlStr( $leftXmlFileToArray[$key] );
                }else{
                    $leftDiff = $leftXmlFileToArray[$key];
                }
                $this->diffResult[] = array(
                    'nodeInfo'      =>$tmpTraceArray,
                    'left'          =>$leftDiff,
                    'right'         =>'',
                );
                continue;
            }
            //如果$rightLineToArray[$key]不为空

            //如果两者相等
            if ( $leftXmlFileToArray[$key] == $rightXmlFileToArray[$key] ){
                continue;
            }
            //如果两者存在且不相等,递归对比
            $this->findDiff( $leftXmlFileToArray[$key], $rightXmlFileToArray[$key], $tmpTraceArray );
        }

        //将isset($leftLineToArray[$key1])==false && isset($rightLineToArray[$key2])==true时的diff写入diff数组
        foreach( $rightXmlFileToArray as $key => $value ){
            $tmpTraceArray = $trackNodeArray;
            $tmpTraceArray[] = $key;
            if ( !array_key_exists( $key, $leftXmlFileToArray ) ){
                if ( is_array( $rightXmlFileToArray[$key] ) ){
                    $rightDiff = $this->arrayToXmlStr( $rightXmlFileToArray[$key] );
                }else{
                    $rightDiff = $rightXmlFileToArray[$key];
                }
                $this->diffResult[] = array(
                    'nodeInfo'      =>$tmpTraceArray,
                    'left'          =>'',
                    'right'         =>$rightDiff,
                );
            }
        }
    }


    /**
     * @bref:将数组转换成xml的字符串
     * @param $arrayVar
     * @return string
     */
    private function arrayToXmlStr( $arrayVar ){
        $result = '';
        if( !is_array( $arrayVar ) || !count( $arrayVar)>0 ){
            return $result;
        }
        foreach( $arrayVar as $key => $value ){
            if ( is_array( $value ) ){
                //如果$value是数组,递归解析数组为字符串
                $value = $this->arrayToXmlStr( $value );
            }
            $result .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        return $result;
    }

    /**
     * @bref:将两个xml文件的比较结果写入结果文件中
     */
    public function readDiffResultToFile(){
        $this->resultFileHandle = fopen( $this->resultFile, 'w');

        //将比较的结果头写入文件中
        $lineInfo = 'there are ' . $this->diffCount . 'diff[s], next is the detail differences.' . Utils::LINE_FEED;
        $lineInfo .= '+++++++++++++++' . Utils::LINE_FEED;
        fwrite( $this->resultFileHandle, $lineInfo );

        //开始将两个文件比较的结果写入文件中
        if ( !count( $this->diffResult )>0 ){
            return;
        }
        foreach( $this->diffResult as $key => $value ){
            $lineInfo = '---';
            if( is_array( $value['nodeInfo'] ) && count( $value['nodeInfo'])>0 ){
                foreach( $value['nodeInfo'] as $key1 => $value1 ){
                    $lineInfo .= $value1 . '->';
                }
                $lineInfo = substr( $lineInfo, 0, strlen( $lineInfo ) -2 );
            }
            $lineInfo .= Utils::LINE_FEED . Utils::SEPARATOR_FEED . Utils::SEPARATOR_FEED . '>' . $value['left'];
            $lineInfo .= Utils::LINE_FEED . Utils::SEPARATOR_FEED . Utils::SEPARATOR_FEED . '<' . $value['right'] . Utils::LINE_FEED;
            fwrite( $this->resultFileHandle, $lineInfo );
            unset( $lineInfo );
        }
    }

    /**
     * @bref:关闭结果写入文件的句柄
     */
    public function closeFileHandle(){

        fclose( $this->resultFileHandle );
    }


    /**
     * @bref:为没有添加xml文件头的xml字符串加上xml文件头
     * @param $xmlString
     *
     */
    private function dealXmlString( &$xmlString ){
        if ( strpos( $xmlString, '<?xml' ) !== 0 ){
            $xmlString = '<?xml version=\'1.0\' encoding="utf-8"?>' . $xmlString;
        }
    }


}