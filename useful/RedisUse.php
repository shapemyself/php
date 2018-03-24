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


//简单极速器的使用
function sampleCounter() {
    $redis = getConnection();
    $strKey = 'Test_bihu_comments';

//设置初始值
    $redis->set($strKey, 0);

    $redis->INCR($strKey);  //+1
    $redis->INCR($strKey);  //+1
    $redis->INCR($strKey);  //+1

    $strNowCount = $redis->get($strKey);

    echo "---- 当前数量为{$strNowCount}。 ---- ";
}

//简单计数器的使用
function sampleSort() {
    $redis = getConnection();

    $strKey = 'Test_bihu_score';

//存储数据
    $redis->zadd($strKey, '50', json_encode(['name' => 'Tom']));
    $redis->zadd($strKey, '70', json_encode(['name' => 'John']));
    $redis->zadd($strKey, '90', json_encode(['name' => 'Jerry']));
    $redis->zadd($strKey, '30', json_encode(['name' => 'Job']));
    $redis->zadd($strKey, '100', json_encode(['name' => 'LiMing']));

    $dataOne = $redis->ZREVRANGE($strKey, 0, -1, true);
    echo "---- {$strKey}由大到小的排序 ---- <br /><br />";
    print_r($dataOne);

    $dataTwo = $redis->ZRANGE($strKey, 0, -1, true);
    echo "<br /><br />---- {$strKey}由小到大的排序 ---- <br /><br />";
    print_r($dataTwo);
}



//简单字符串悲观锁


function lock($key = '', $expire = 5) {
    $redis = getConnection();
    //setnax(key,value):  将 key 的值设为 value ，当且仅当 key 不存在。
    $is_lock = $redis->setnx($key, time()+$expire);
    //不能获取锁
    if(!$is_lock){
        //判断锁是否过期
        $lock_time = $redis->get($key);
        //锁已过期，删除锁，重新获取
        if (time() > $lock_time) {
            unlock($key);
            $is_lock = $redis->setnx($key, time() + $expire);
        }
    }

    return $is_lock? true : false;
}

/**
 * 释放锁
 * @param  String  $key 锁标识
 * @return Boolean
 */
function unlock($key = ''){
    $redis = getConnection();
    return $redis->del($key);
}


function sampleUnhappyLock() {
    // 定义锁标识
    $key = 'Test_bihu_lock';

    // 获取锁
    $is_lock = lock($key, 10);
    if ($is_lock) {
        echo 'get lock success<br>';
        echo 'do sth..<br>';
        sleep(5);
        echo 'success<br>';
        unlock($key);
    } else { //获取锁失败
        echo 'request too frequently<br>';
    }

}


/**
 * @bref:乐观锁的实现，乐观锁的意思是：每次修改前都假设不会发生修改
 */
function sampleHappyKey() {

    $redis = getConnection();

    $strKey = 'Test_bihu_age';

    $redis->set($strKey,10);

    $age = $redis->get($strKey);

    echo "---- Current Age:{$age} ---- <br/><br/>";

    $redis->watch($strKey,10);

// 开启事务
    $redis->multi();

//在这个时候新开了一个新会话执行
    $redis->set($strKey,30);  //新会话

    echo "---- Current Age:{$age} ---- <br/><br/>"; //30

    $redis->set($strKey,20);

    $redis->exec();

    $age = $redis->get($strKey);

    echo "---- Current Age:{$age} ---- <br/><br/>"; //30

//当exec时候如果监视的key从调用watch后发生过变化，则整个事务会失败

}

sampleHappyKey();








