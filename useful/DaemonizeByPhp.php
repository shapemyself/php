<?php
/**
 * Created by PhpStorm.
 * User: liuhengsheng
 * Date: 2018/3/25
 * Time: 14:20
 */

/**
 * 一般Server程序都是运行在系统后台，这与普通的交互式命令行程序有很大的区别。glibc里有一个函数daemon。调用此函数，就可使当前进程脱离终端变成一个守护进程，具体内容参见man daemon。PHP中暂时没有此函数，当然如果你有兴趣的话，可以写一个PHP的扩展函数来实现。
 *
 * PHP命令行程序实现守护进程化有2种方法：s
 * 使用nohup
 * nohup php myprog.php > log.txt &
 *
 * 这里就实现了守护进程化。单独执行 php myprog.php，当按下ctrl+c时就会中断程序执行，会kill当前进程以及子进程。php myprog.php &，这样执行程序虽然也是转为后台运行，实际上是依赖终端的，当用户退出终端时进程就会被杀掉。
 *
 */
//使用PHP代码来实现

function daemonize()
{
	$pid = pcntl_fork();
	if ($pid == -1)
	{
		die("fork(1) failed!\n");
	}
	elseif ($pid > 0)
	{
//让由用户启动的进程退出
		exit(0);
	}

//建立一个有别于终端的新session以脱离终端
	posix_setsid();

	$pid = pcntl_fork();
	if ($pid == -1)
	{
		die("fork(2) failed!\n");
	}
	elseif ($pid > 0)
	{
//父进程退出, 剩下子进程成为最终的独立进程
		exit(0);
	}
}

daemonize();
sleep(1000);