<?php
/**
 *
 * @bref: redis的一些常用的使用场景
 *
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 2017/2/25
 * Time: 下午10:34
 */
function getConnection() {
    $redis = new redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth('liuhengsheng');
    return $redis;
}

/**
 * 简单的字符串缓存
 */

/**
 * set应用
 */
function cacheStrSet() {
    $redis = getConnection();
    $strCacheKey  = 'Test_bihu_set';

    $arrCacheData = [
        'name' => 'job',
        'sex'  => '男',
        'age'  => '30'
    ];
    $redis->set($strCacheKey, json_encode($arrCacheData));
    $redis->expire($strCacheKey, 30);  # 设置30秒后过期
    $json_data = $redis->get($strCacheKey);
    $data = json_decode($json_data);
    print_r($data->age); //输出数据

}

/**
 * hset的使用
 */
function cacheHsetStr() {
    $redis = getConnection();
    $strCacheKey  = 'Test_bihu_hset';

    //HSET 应用
    $arrWebSite = [
        'google' => [
            'google.com',
            'google.com.hk'
        ],
    ];
    $redis->hSet($strCacheKey, 'google', json_encode($arrWebSite['google']));
    $json_data = $redis->hGet($strCacheKey, 'google');
    $data = json_decode($json_data);
    print_r($data); //输出数据
}


/**
 * 队列的使用
 */
function sampleUseQuen() {
    $redis = getConnection();
    //进队列
    $strQueueName  = 'Test_bihu_queue';
    $redis->rpush($strQueueName, json_encode(['uid' => 1,'name' => 'Job']));
    $redis->rpush($strQueueName, json_encode(['uid' => 2,'name' => 'Tom']));
    $redis->rpush($strQueueName, json_encode(['uid' => 3,'name' => 'John']));
    echo "---- 进队列成功 ---- <br /><br />";

//查看队列
    $strCount = $redis->lrange($strQueueName, 0, -1);
    echo "当前队列数据为： <br />";
    print_r($strCount);

//出队列
    $redis->lpop($strQueueName);
    echo "<br /><br /> ---- 出队列成功 ---- <br /><br />";

//查看队列
    $strCount = $redis->lrange($strQueueName, 0, -1);
    echo "当前队列数据为： <br />";
    print_r($strCount);
}


/**
 * 简单发布订阅实战
 */
function samplePublish() {
    $redis = getConnection();
    $strChannel = 'Test_bihu_channel';
//发布
    $redis->publish($strChannel, "来自{$strChannel}频道的推送");
    echo "---- {$strChannel} ---- 频道消息推送成功～ <br/>";
    $redis->close();
}

function sampleSub() {
    $redis = getConnection();

    $strChannel = 'Test_bihu_channel';

//订阅
    echo "---- 订阅{$strChannel}这个频道，等待消息推送...----  <br/><br/>";
    $redis->subscribe([$strChannel], 'callBackFun');
    $redis->close();
}


function callBackFun($redis, $channel, $msg)
{
    print_r([
        'redis'   => $redis,
        'channel' => $channel,
        'msg'     => $msg
    ]);
}

function samplePulishAndSub() {
    $strChannel = 'Test_bihu_channel';
    $pid = pcntl_fork();

    if ( $pid > 0 ) {
        //父进程
        //发布
        $redis = getConnection();
        for($index=0; $index<10; $index++) {
            $redis->publish($strChannel, "来自{$strChannel}频道的推送:$index");
            echo "---- {$strChannel} ---- 频道消息推送成功～ $index<br/>";
            sleep(1);
        }
        $redis->close();
        exit(0);
    }elseif ( $pid == 0 ) {
        //子进程
        $redis = getConnection();
        //订阅
        echo "---- 订阅{$strChannel}这个频道，等待消息推送...----  <br/><br/>";
        $redis->subscribe([$strChannel], 'callBackFun');
        echo "----------------------------------------------------------------";
        $redis->close();
        exit(0);
    }else{
        echo '进程创建失败',PHP_EOL;
        exit(-1);
    }
}






samplePulishAndSub();





