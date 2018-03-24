<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 16/8/20
 * Time: 下午5:21
 */

/**
 * @bref:删除一个目录下所有的文件以及文件下的目录,如果$baseDir=false会将当前文件夹都删掉
 * @param $dir:待处理的文件夹
 * @param $baseDir bool:用于区分是传入的文件夹还是文件夹下的子文件夹
 * @author liuhengsheng
 */
function delAllFileOfDir($dir,$baseDir=true) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                delAllFileOfDir($fullpath, false);
            }
        }
    }
    @closedir($dh);
    if ( !$baseDir ) {
        rmdir($dir);
    }
}


/**
 * @bref:将一个文件夹下的所有的文件进行zip打包
 * @param $fileDir 需要打包的文件夹
 * @param $resultZipFile 最后生成的ZIP包文件的路径
 * @return bool
 * @author liuhengsheng
 */
function zipDirectory($fileDir,$resultZipFile) {
    if ( !file_exists($fileDir) ) {
        return false;
    }
    //删除已有的压缩文件
    if ( file_exists($resultZipFile) ) {
        unlink($resultZipFile);
    }
    fclose(fopen($resultZipFile,'w+'));
    $zip = new ZipArchive();
    //ZipArchive::OVERWRITE表示如果zip文件存在，就覆盖掉原来的zip文件。
    //如果参数使用ZIPARCHIVE::CREATE，系统就会往原来的zip文件里添加内容。
    //如果不是为了多次添加内容到zip文件，建议使用ZipArchive::OVERWRITE
    if ( $zip->open(ZIPFILE,ZipArchive::CREATE)===true ) {
        addFileToZip(OUTPUTDIR, $zip,BASEDIR);
        $zip->close();
    }
    return true;
}

/**
 * @bref:将文件或者目录下的文件加入到打包文件中,
 * $zip->addFile($file)和$zip->addFile($file,$baseDir)的
 * 区别在于,如果采用第一种,解压后的文件夹的最外层目录是根目录/,而如果采用的是第二种,解压后的文件夹的最外层目录是压缩前的
 * 最外层目录的父目录,显然我们想要的是第二种
 * @param $path 待打包的文件或者文件夹
 * @param $zip zip包处理流
 * @param $baseDir 最外层目录的父目录
 * @author liuhengsheng
 */
function addFileToZip($path,$zip,$baseDir){
    $tempdirs = explode($baseDir, $path);
    $handler=opendir($path); //打开当前文件夹由$path指定。
    while(($filename=readdir($handler))!==false){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                addFileToZip($path."/".$filename, $zip,$baseDir);
            }else{ //将文件加入zip对象
                $zip->addFile($path."/".$filename,$tempdirs[1].'/'.$filename);
            }
        }
    }
    @closedir($path);
}


/**
 * @bref:获取一个文件夹下面的所有文件的数组,除了隐藏文件(.xx)和备份文件(~xx)
 * @param $path
 * @param $files
 * @author liuhengsheng
 */
function get_allfiles($path,&$files) {
    if(is_dir($path)){
        $dp = dir($path);
        while ($file = $dp ->read()){
            if($file !="." && $file !=".."){
                get_allfiles($path."/".$file, $files);
            }
        }
        $dp ->close();
    }
    if( is_file($path) ){
        $temp = explode('/',$path);
        $fileName = array_pop($temp);
        if ( strpos($fileName, '.') !== 0 && strpos($fileName,'~')!==0 ) {
            $files[] = $path;
        }
    }
}

