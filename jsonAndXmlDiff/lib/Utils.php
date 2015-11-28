<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 15/10/15
 * Time: 下午8:03
 */





class Utils{

    const DIFF_VERSION = '1.0.1';

    const CMD_ERR_PROMPT = "ERROR: the diff cmd input is not legel,please review the help info and input again\n\n";

    const CMD_FILE_NOT_EXIT_ERROR = "ERROR: the files to be compared is not exist";

    //当比较的文件是xml时
    const JSON_TYPE = 1;
    //当比较的文件是json时
    const XML_TYPE = 2;

    //换行符号
    const LINE_FEED = "\n";

    //分隔符
    const SEPARATOR_FEED = "\t";

    //双引号
    const DOUBLE_QUOTTE = "\"";

    public static $logFileName = '';

    /**
     * 打印diff程序的版本信息
     */
    public static function getVersion (){
        echo 'diff version: ' . self::DIFF_VERSION . "\n";
    }

    /**
     * 打印diff程序的帮助信息
     */
    public static function showHelp (){
        echo "Usage:\t diff:\t php diff.php -t [json|xml] -l fileName1 -r fileName2 -o outputFileName [-e utf8] \n",
             "\t help:\t php diff.php -h\n",
             "\t version:  php diff.php -v\n",
             "\t\t -t\t[json|xml]:\tstructure type of file content\n",
             "\t\t -l\tfileName1:\tleft compared fileName\n",
             "\t\t -r\tfileName2:\tright compared fileName\n",
             "\t\t -o\toutputFileName:\tthe fileName of diff result info\n",
             "\t\t -e\t[utf8|gbk]:\tthe encode type of file\n";
    }

    /**
     * @bref:校验命令行输入命令的合法性
     * @param $inputCmd 输入的命令命令参数数组
     * @return bool
     */
    public static function checkInputCmd (&$inputCmd){
        if (!is_array($inputCmd) || !count($inputCmd)>1){
            return false;
        }
        //校验输入的命令的参数的完整性
        if (!array_key_exists('t',$inputCmd)){
            return false;
        }
        if (!array_key_exists('l',$inputCmd)){
            return false;
        }
        if (!array_key_exists('r',$inputCmd)){
            return false;
        }
        if (!array_key_exists('o',$inputCmd)){
            return fasle;
        }
        if (!array_key_exists('e',$inputCmd)){
            //默认的编码格式为utf-8
            $inputCmd['e'] = 'utf8';
        }

        //校验各个参数对应的值的合法性
        foreach($inputCmd as $key => $value ){
            switch($key){
                case 't':
                    if (strcasecmp($value,'json')!==0 && strcasecmp($value,'xml')!==0){
                        return false;
                    }
                    break;
                case 'l':
                    if (empty($value)){
                        return false;
                    }
                    break;
                case 'r':
                    if (empty($value)){
                        return false;
                    }
                    break;
                case 'o':
                    if (empty($value)){
                        return false;
                    }
                    break;
                case 'e':
                    if (strcasecmp($value,'utf8')!==0 && strcasecmp($value,'gbk')!==0){
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }
        return true;
    }

    /**
     * @bref:判断输入的文件路径是否存在
     * @param array $cmdOptions
     * @return bool
     */
    public static function isFilesExist(&$cmdOptions = array()) {
        if ( !is_array($cmdOptions) ){
            return false;
        }
        //如果不是绝对路径则加上绝对路径,相对路径的目录是resource/目录
        if ( strpos( $cmdOptions['l'], '/' ) === false ){
            $cmdOptions['l'] = substr( __DIR__, 0, strrpos(__DIR__, '/') ) . '/resource/' . $cmdOptions['l'];
        }
        //如果不是绝对路径则加上绝对路径,相对路径的目录是resource/目录
        if ( strpos( $cmdOptions['r'], '/' ) === false ){
            $cmdOptions['r'] = substr( __DIR__, 0, strrpos(__DIR__, '/') ) . '/resource/' . $cmdOptions['r'];
        }
        if ( !file_exists($cmdOptions['l']) || !file_exists($cmdOptions['r']) ){
            return false;
        }
        //如果不是绝对路径则加上绝对路径,相对路径的目录是resource/目录
        if ( strpos( $cmdOptions['o'], '/' ) === false ){
            $cmdOptions['o'] = substr( __DIR__, 0, strrpos(__DIR__, '/') ) . '/resource/' . $cmdOptions['o'];
        }
        if ( !file_exists($cmdOptions['o']) ){
            $tmpFileHandle = fopen($cmdOptions['o'],'w');
            fclose($tmpFileHandle);
        }
        return true;
    }

    /**
     * @bref:设置日志文件的名称
     *
     */
    public static function setLogFileName() {
        if ( empty(Utils::$logFileName) ){
            $dateStr = date('Y-m-d', time());
            Utils::$logFileName = $dateStr . 'log.txt';
        }
    }


}


















